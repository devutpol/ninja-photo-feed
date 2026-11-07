<?php

namespace NinjaPhotoFeed\App\Services\Platforms;

use NinjaPhotoFeed\App\Services\DataProtector;
use NinjaPhotoFeed\App\Services\Helper as ServicesHelper;
use NinjaPhotoFeed\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Instagram-only version of core's RemoteAuth. refreshAuth()/isReconnectError()/bridgeIdentity()/
 * refreshError()/canonicalJson() are dropped: they back server-to-server token refresh for
 * Google and TikTok feeds, which this build doesn't ship. Instagram refreshes its own tokens
 * through Feeds/Instagram/RefreshToken.php instead. The connect handshake below
 * (prepareAuth/consumeAuth) is generic and still used by Instagram's own connect flow.
 */
class RemoteAuth
{
    /** This site's bridge identity { site_id, site_secret }, secret encrypted at rest. */
    const IDENTITY_OPTION = 'npfeed_auth_bridge_identity';

    /**
     * Store the per-site credential from the connect handshake. A result without one
     * (older bridge) leaves the existing identity in place.
     *
     * @param mixed $result
     * @return void
     */
    private static function storeBridgeIdentity($result)
    {
        if (!is_array($result)) {
            return;
        }

        $siteId     = (string) Arr::get($result, 'site_id', '');
        $siteSecret = (string) Arr::get($result, 'site_secret', '');

        if (empty($siteId) || empty($siteSecret)) {
            return;
        }

        update_option(self::IDENTITY_OPTION, [
            'site_id'     => $siteId,
            'site_secret' => self::protectValue($siteSecret),
        ], 'no');
    }

    public static function prepareAuth($platform, $provider, $flow, $settings = [])
    {
        $provider  = Arr::get($settings, 'provider', $provider);
        $flow      = Arr::get($settings, 'flow', $flow);
        $returnUrl = Arr::get($settings, 'return_url', '');
        $source    = Arr::get($settings, 'source', '');

        if (!$provider || !$flow || !$returnUrl) {
            wp_send_json_error([
                'message' => __('Missing auth preparation data.', 'ninja-photo-feed')
            ], 423);
        }

        $siteHost   = wp_parse_url(home_url(), PHP_URL_HOST);
        $returnHost = wp_parse_url($returnUrl, PHP_URL_HOST);
        if (!$returnHost || strtolower($returnHost) !== strtolower($siteHost)) {
            wp_send_json_error([
                'message' => __('Invalid return URL.', 'ninja-photo-feed')
            ], 400);
        }

        $verifier  = ServicesHelper::createAuthVerifier();
        $resultKey = ServicesHelper::createAuthVerifier();
        $flowId    = wp_generate_uuid4();
        $challenge = ServicesHelper::base64UrlEncode(hash('sha256', $verifier, true));

        set_transient(self::transientKey($platform, $flowId), [
            'verifier'   => $verifier,
            'result_key' => self::protectValue($resultKey),
            'platform'   => $platform,
            'provider'   => $provider,
            'flow'       => $flow,
        ], 15 * MINUTE_IN_SECONDS);

        // Sent as a POST form (not a query string) so auth_result_key never lands in a URL,
        // browser history, or server logs.
        // Core fields go last so the user-fields filter can never override them.
        $fields = array_merge(self::bridgeUserFields(), [
            'return_url'      => $returnUrl,
            'auth_flow_id'    => $flowId,
            'auth_challenge'  => $challenge,
            'auth_result_key' => $resultKey,
            'source'          => $source,
        ]);
        $action = ServicesHelper::authBaseUrl() . '/connect/' . rawurlencode($provider) . '/' . rawurlencode($flow);

        wp_send_json_success([
            'action'  => $action,
            'fields'  => $fields,
            'flow_id' => $flowId,
        ], 200);
    }

    /**
     * Connecting admin + plugin tier, recorded by the bridge for connected-user tracking only
     * (never echoed back in the result payload). Filterable so site owners can drop or alter it.
     *
     * @return array<string, string>
     */
    private static function bridgeUserFields()
    {
        $user = wp_get_current_user();

        $fields = [
            'wp_user_email'   => $user && $user->exists() ? (string) $user->user_email : '',
            'wp_display_name' => $user && $user->exists() ? (string) $user->display_name : '',
            'plugin_tier'     => defined('NINJAPHOTOFEED_PRO') ? 'pro' : 'free',
        ];

        $fields = apply_filters('ninjaphotofeed/auth_bridge_user_fields', $fields, $user);
        if (!is_array($fields)) {
            return [];
        }

        // Only these three keys may reach the bridge: anything else (core auth fields, bridge
        // control fields like launch or marketing_*) would change the connect flow itself.
        $fields = array_intersect_key($fields, array_flip(['wp_user_email', 'wp_display_name', 'plugin_tier']));

        return array_map('strval', array_filter($fields, 'is_scalar'));
    }

    public static function consumeAuth($platform, $settings, callable $persistCallback, callable $formatCallback)
    {
        $payload = Arr::get($settings, 'payload', '');
        $flowId  = Arr::get($settings, 'flow_id', '');

        if (!$payload || !$flowId) {
            wp_send_json_error([
                'message' => __('Missing auth result data.', 'ninja-photo-feed')
            ], 423);
        }

        $transientKey = self::transientKey($platform, $flowId);
        $prepared     = get_transient($transientKey);

        if (empty($prepared['verifier']) || Arr::get($prepared, 'platform') !== $platform) {
            wp_send_json_error([
                'message' => __('Auth verifier has expired. Please try connecting again.', 'ninja-photo-feed')
            ], 423);
        }

        try {
            $result = self::decryptPayload($payload, $prepared);
            delete_transient($transientKey);

            // Store the refresh credential, then drop the secret so it never lands in platform token configs.
            self::storeBridgeIdentity($result);
            if (is_array($result)) {
                unset($result['site_secret']);
            }

            $result = $persistCallback($result);

            wp_send_json_success([
                'message' => __('Authentication completed successfully.', 'ninja-photo-feed'),
                'result'  => $formatCallback($result),
            ], 200);
        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 423);
        }
    }

    private static function decryptPayload($payload, $prepared)
    {
        $resultKey = self::unprotectValue(Arr::get($prepared, 'result_key', ''));

        if (empty($resultKey) || empty($prepared['verifier'])) {
            throw new \RuntimeException(esc_html__('Auth verifier has expired. Please try connecting again.', 'ninja-photo-feed'));
        }

        $json   = ServicesHelper::decryptAuthPayload($payload, $resultKey);
        $result = json_decode($json, true);
        if (!is_array($result)) {
            throw new \RuntimeException(esc_html__('Invalid auth payload data.', 'ninja-photo-feed'));
        }

        return ServicesHelper::verifyAuthConsumeChallenge($result, $prepared['verifier']);
    }

    private static function transientKey($platform, $flowId)
    {
        return 'npfeed_auth_' . $platform . '_' . get_current_user_id() . '_' . sanitize_key($flowId);
    }

    private static function protectValue($value)
    {
        $protected = (new DataProtector())->encrypt($value);
        return $protected ?: $value;
    }

    private static function unprotectValue($value)
    {
        $unprotected = (new DataProtector())->decrypt($value);
        return $unprotected ?: $value;
    }
}
