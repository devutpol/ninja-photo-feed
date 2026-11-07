<?php

namespace NinjaPhotoFeed\App\Http\Controllers;

use NinjaPhotoFeed\App\Services\Platforms\Feeds\CacheHandler;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\Framework\Http\Request\Request;
use NinjaPhotoFeed\App\Services\GlobalSettings;
use NinjaPhotoFeed\App\Services\Platforms\PlatformManager;
use NinjaPhotoFeed\App\Hooks\Handlers\UninstallHandler;
use NinjaPhotoFeed\App\Services\PermissionManager;

/**
 * Instagram-only version of core's SettingsController. Twitter cards, translations,
 * license management, QR codes and AI review summarizer are dropped: no route or
 * admin screen in this build reaches them.
 */
class SettingsController extends Controller
{
    // Each screen names the capabilities that reach it, so holding one screen's
    // capability doesn't grant another screen's routes.
    protected $screenCapabilities = [
        'advance_settings' => ['npfeed_feeds_advance_settings', 'npfeed_reviews_platforms_settings'],
        'delete_all_data'  => ['npfeed_full_access'],
        'error_log'        => ['npfeed_manage_platforms'],
    ];

    /**
     * Refuse the request unless the user holds one of the given capabilities.
     * Returns an error response to return early with, or null when allowed.
     */
    protected function denyUnlessAllowed($capabilities)
    {
        foreach ((array) $capabilities as $capability) {
            if (PermissionManager::currentUserCan($capability)) {
                return null;
            }
        }

        return $this->sendError([
            'message' => __('You do not have permission to manage these settings.', 'ninja-photo-feed')
        ], 403);
    }

    protected function denyUnlessScreenAllowed($screen)
    {
        return $this->denyUnlessAllowed($this->screenCapabilities[$screen]);
    }

    /**
     * Capabilities owning a settings platform. Empty for anything this install does
     * not serve, so a hook name is never built from an unrecognised identifier.
     */
    protected function platformCapabilities($platform)
    {
        if ($platform === '') {
            return [];
        }

        $manager = new PlatformManager();
        $map = [];

        foreach ($manager->feedPlatforms() as $feedPlatform) {
            $map[$feedPlatform] = ['npfeed_feeds_platforms_settings'];
        }

        $reviewPlatforms = array_merge(
            $manager->reviewsPlatforms(),
            array_keys((array) get_option('npfeed_available_valid_platforms', []))
        );

        foreach ($reviewPlatforms as $reviewPlatform) {
            $map[$reviewPlatform] = ['npfeed_reviews_platforms_settings'];
        }

        // resetData() treats 'reviews' as the aggregate over every review platform.
        $map['reviews'] = ['npfeed_reviews_platforms_settings'];

        $map = apply_filters('ninjaphotofeed/settings_platform_capabilities', $map);

        return isset($map[$platform]) ? (array) $map[$platform] : [];
    }

    /**
     * Refuse unless the user holds a capability owning this request's platform.
     */
    protected function denyUnlessPlatformAllowed($platform)
    {
        $capabilities = $this->platformCapabilities($platform);

        if (empty($capabilities)) {
            return $this->sendError([
                'message' => __('Invalid platform.', 'ninja-photo-feed')
            ], 403);
        }

        return $this->denyUnlessAllowed($capabilities);
    }

    public function index(Request $request)
    {
        $platform = sanitize_text_field($request->get('platform', ''));

        if ($denied = $this->denyUnlessPlatformAllowed($platform)) {
            return $denied;
        }

        do_action('ninjaphotofeed/get_advance_settings_' . $platform);
    }

    public function update(Request $request)
    {
        $platform = sanitize_text_field($request->get('platform', ''));

        if ($denied = $this->denyUnlessPlatformAllowed($platform)) {
            return $denied;
        }

        $settings = $request->get('settings');
        if (is_string($settings)) {
            $settings = $this->decodeJsonParam($settings) ?: [];
        }

        // Only the keys BaseFeed::formatPlatformGlobalSettings() reads; this build has no
        // review platform for the reviews-only keys core's sanitize map also carries.
        $sanitizeMap = [
            'expiration' => 'intval',
            'oembed' => 'npfeed_sanitize_boolean',
            'optimized_images' => 'npfeed_sanitize_boolean',
            'is_enabled_platform' => 'npfeed_sanitize_boolean',
        ];

        $settings = npfeed_backend_sanitizer($settings, $sanitizeMap);

        do_action('ninjaphotofeed/save_advance_settings_' . $platform, $settings);
    }

    public function delete(Request $request)
    {
        $platform = sanitize_text_field($request->get('platform', ''));

        if ($denied = $this->denyUnlessPlatformAllowed($platform)) {
            return $denied;
        }

        $cacheType = sanitize_text_field($request->get('cacheType', ''));
        $templateId = intval($request->get('templateId'));

        if ($cacheType === 'template' && $templateId) {
            return (new CacheHandler($platform))->clearTemplateCache($platform, $templateId);
        }

        do_action('ninjaphotofeed/clear_cache_' . $platform, $cacheType);
    }

    public function getAdvanceSettings()
    {
        if ($denied = $this->denyUnlessScreenAllowed('advance_settings')) {
            return $denied;
        }

        $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');

        if (is_array($advanceSettings) && !PermissionManager::currentUserCan('npfeed_manage_qr_codes')) {
            unset($advanceSettings['qr_codes']);
        }

        return [
            'message'           => 'success',
            'advance_settings'  => $advanceSettings,
        ];
    }

    public function saveAdvanceSettings(Request $request)
    {
        if ($denied = $this->denyUnlessScreenAllowed('advance_settings')) {
            return $denied;
        }

        $advanceSettings = $request->get('advance_settings');

        if (is_string($advanceSettings)) {
            $advanceSettings = $this->decodeJsonParam($advanceSettings) ?: [];
        }

        $sanitizeMap = [
             'has_gdpr'  => 'npfeed_sanitize_boolean',
             'optimize_image_format'  => 'sanitize_text_field',
             'review_optimized_images'  => 'npfeed_sanitize_boolean',
             'preserve_plugin_data'  => 'npfeed_sanitize_boolean',

             // nested email_report keys
             'email_report.status' => 'npfeed_sanitize_boolean',
             'email_report.sending_day' => 'sanitize_text_field',
             'email_report.recipients' => 'npfeed_sanitize_recipients',
        ];

        $advanceSettings = npfeed_backend_sanitizer($advanceSettings, $sanitizeMap);

        // qr_codes rides along in this payload but belongs to the QR screen, so keep
        // the stored list for anyone who cannot manage it on its own routes.
        if (!PermissionManager::currentUserCan('npfeed_manage_qr_codes')) {
            $advanceSettings['qr_codes'] = (new GlobalSettings())->getGlobalSettings('advance_settings.qr_codes');
        }

        $settings = get_option('npfeed_global_settings', []);

        $oldOptimizeImageFormat = Arr::get($settings, 'global_settings.advance_settings.optimize_image_format', '');
        $newOptimizeImageFormat = Arr::get($advanceSettings, 'optimize_image_format');

        if ($newOptimizeImageFormat != $oldOptimizeImageFormat) {
            $this->resetDataForOptimizeFormatChange($request);
        }

        $settings['global_settings']['advance_settings'] = $advanceSettings;
        $optimized_images = Arr::get($settings, 'global_settings.advance_settings.review_optimized_images', 'false');

        if($optimized_images == 'true') {
            $has_npfeed_optimize_images_table = get_option( 'npfeed_optimize_images_table_status', false);
            $older_version = get_option('_ninja_photo_feed_version', '1.0.0');
            if(version_compare($older_version, '3.15.0', '<=') && $optimized_images === 'true' && !$has_npfeed_optimize_images_table){
                \NinjaPhotoFeed\Database\Migrations\ImageOptimizationMigrator::migrate();
            }
        }

        $globalSettings = (new GlobalSettings())->formatGlobalSettings($settings);
        update_option('npfeed_global_settings', $globalSettings);

        return [
            'message'   =>  __('Settings saved successfully!', 'ninja-photo-feed')
        ];
    }

    public function resetDataForOptimizeFormatChange(Request $request)
    {
        $manager = new PlatformManager();
        $platforms = $manager->getPlatformsListWithReviewAlias();
        foreach ($platforms as $platform) {
            $this->performResetData($platform);
        }
    }

    /**
     * Reset one platform's images. Callers are responsible for authorization —
     * saveAdvanceSettings() reaches this after its own capability check.
     */
    protected function performResetData($platform)
    {
        if($platform == 'reviews'){
            $platforms = apply_filters('ninjaphotofeed/available_valid_reviews_platforms', []);
            do_action('ninjaphotofeed/review_reset_data', $platforms);
        }else{
            do_action('ninjaphotofeed/reset_data', $platform);
        }
    }

    public function resetData(Request $request)
    {
        $platform = sanitize_text_field($request->get('platform', ''));

        if ($denied = $this->denyUnlessPlatformAllowed($platform)) {
            return $denied;
        }

        $this->performResetData($platform);

        return [
            'message'   =>  __('Images reset successfully!', 'ninja-photo-feed')
        ];
    }

    public function resetErrorLog(Request $request)
    {
        // SettingsPolicy::resetErrorLog() gates this route by name. Repeated here so a
        // rename of this method cannot silently drop it back to the group gate.
        if ($denied = $this->denyUnlessScreenAllowed('error_log')) {
            return $denied;
        }

        delete_option('npfeed_errors');
        return [
            'message'   =>  __('Reset Error Logs successfully!', 'ninja-photo-feed')
        ];
    }

    public function deleteAllData()
    {
        if ($denied = $this->denyUnlessScreenAllowed('delete_all_data')) {
            return $denied;
        }

        (new UninstallHandler())->deleteAllPlatformsData(false);
        return [
            'message'   =>  __('Successfully deleted all data!', 'ninja-photo-feed')
        ];
    }
}
