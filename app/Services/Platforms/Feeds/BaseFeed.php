<?php

namespace NinjaPhotoFeed\App\Services\Platforms\Feeds;

use NinjaPhotoFeed\App\Models\Cache;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\DataProtector;
use NinjaPhotoFeed\App\Services\GlobalSettings;
use NinjaPhotoFeed\App\Services\Platforms\PlatformManager;
use NinjaPhotoFeed\App\Services\Platforms\RemoteAuth;

abstract class BaseFeed
{
    public $platform;
    protected $authProvider = '';
    protected $authFlow = '';

    /** @var CacheHandler|null Set by each platform feed in its own constructor. */
    protected $cacheHandler;

    /**
     * Refresh the given expired caches. Each platform builds its own API calls.
     *
     * @param array $caches
     * @return void
     */
    abstract public function updateCachedFeeds($caches);

    public function __construct($platform)
    {
        $this->platform = $platform;
    }

    public function registerHooks()
    {
        add_filter('ninjaphotofeed/available_valid_feed_platforms', array($this, 'pushValidPlatform'));

        add_action('ninjaphotofeed/verify_credential_' . $this->platform, array($this, 'handleCredential'));
        add_action('ninjaphotofeed/get_verification_configs_' . $this->platform, array($this, 'getVerificationConfigs'));
        add_action('ninjaphotofeed/clear_verification_configs_' . $this->platform, array($this, 'clearVerificationConfigs'));


        if ($this->authProvider) {
            add_action('ninjaphotofeed/prepare_feed_auth_' . $this->platform, array($this, 'prepareAuth'));
            add_action('ninjaphotofeed/consume_feed_auth_' . $this->platform, array($this, 'consumeAuth'));
        }



        add_action('ninjaphotofeed/get_editor_settings_' . $this->platform, array($this, 'getEditorSettings'));
        add_action('ninjaphotofeed/edit_editor_settings_' . $this->platform, array($this, 'editEditorSettings'), 10, 2);
        add_action('ninjaphotofeed/update_editor_settings_' . $this->platform, array($this, 'updateEditorSettings'), 10, 2);

        add_action('ninjaphotofeed/save_advance_settings_' . $this->platform, array($this, 'saveAdvanceSettings'));
        add_action('ninjaphotofeed/get_advance_settings_' . $this->platform, array($this, 'getAdvanceSettings'));
        add_action('ninjaphotofeed/clear_cache_' . $this->platform, array($this, 'clearCache'));

        add_action('npfeed_' . $this->platform . '_feed_update', array($this, 'doCronEvent'));
    }

    public function prepareAuth($settings = [])
    {
        if (!$this->authProvider) {
            wp_send_json_error([
                'message' => __('Authentication is not supported for this platform.', 'ninja-photo-feed')
            ], 423);
        }
        RemoteAuth::prepareAuth($this->platform, $this->authProvider, $this->authFlow, $settings);
    }

    public function consumeAuth($settings = [])
    {
        if (!$this->authProvider) {
            wp_send_json_error([
                'message' => __('Authentication is not supported for this platform.', 'ninja-photo-feed')
            ], 423);
        }
        RemoteAuth::consumeAuth(
            $this->platform,
            $settings,
            function ($result) { return $this->persistAuthResult($result); },
            function ($result) { return $this->formatAuthResultForResponse($result); }
        );
    }

    protected function persistAuthResult($result)
    {
        throw new \RuntimeException(esc_html__('Authentication is not supported for this platform.', 'ninja-photo-feed'));
    }

    protected function formatAuthResultForResponse($result)
    {
        return [
            'ok'       => (bool) Arr::get($result, 'ok'),
            'platform' => $this->platform,
            'account'  => Arr::get($result, 'account', []),
        ];
    }

    /**
     * Get Advance Settings
     *
     * @return void
     * @since 1.2.5
     */
    public function getAdvanceSettings()
    {
        $settings = get_option('npfeed_' . $this->platform . '_global_settings');
        $settings = $this->formatPlatformGlobalSettings($settings);

        $platforms = ['tiktok'];
        if(!in_array($this->platform, $platforms)) {
            if(isset($settings['app_settings'])) {
                unset($settings['app_settings']);
            }
        }

        wp_send_json_success([
            'message'  => __('success', 'ninja-photo-feed'),
            'settings' => $settings
        ], 200);
    }

    public function saveAdvanceSettings($settings = array())
    {
        $settings = $this->formatPlatformGlobalSettings($settings);
        update_option('npfeed_' . $this->platform . '_global_settings', $settings, false);

        if($this->platform === 'instagram' || $this->platform === 'facebook_feed' || $this->platform === 'tiktok' || $this->platform === 'youtube') {
            $has_npfeed_optimize_images_table = get_option( 'npfeed_optimize_images_table_status', false);
            $optimized_images = Arr::get($settings, 'global_settings.optimized_images');
            $older_version = get_option('_ninja_photo_feed_version', '1.0.0');

            if(version_compare($older_version, '3.10.0', '<=') && $optimized_images === 'true' && !$has_npfeed_optimize_images_table){
                \NinjaPhotoFeed\Database\Migrations\ImageOptimizationMigrator::migrate();
            }
        }

        update_option('npfeed_' . $this->platform . '_global_settings', $settings, 'no');
        wp_send_json_success([
            'message' => __('Settings Saved Successfully', 'ninja-photo-feed'),
        ], 200);
    }

    public function formatPlatformGlobalSettings($settings = [])
    {
        $expiration = 60*60*6;
        if($this->platform === 'youtube'){
            $expiration *= 12;
        }

        $configs = [
            'global_settings'   => [
                'caching_type'        => Arr::get($settings, 'global_settings.caching_type', 'background'),
                'expiration'          => Arr::get($settings, 'global_settings.expiration', $expiration),
                'oembed'              => Arr::get($settings, 'global_settings.oembed', 'false'),
                'optimized_images'    => Arr::get($settings, 'global_settings.optimized_images', 'false'),
                'is_enabled_platform' => (bool) (new PlatformManager())->isActivePlatform($this->platform)
            ],
        ];

        return $configs;
    }

	public function doCronEvent()
	{
		$expiredCaches = $this->cacheHandler->getExpiredCaches();

		if ($expiredCaches) {
			$caches = [];

			foreach ($expiredCaches as $name => $cache) {
				$caches[] = [
					'option_name' => $name,
					'option_value' => $cache
				];
			}

			$this->updateCachedFeeds($caches);
		}
	}

    public function getGdprSettings($platform)
    {
        $global_settings = get_option('npfeed_'.$platform.'_global_settings');
        $gdpr_settings = (new GlobalSettings())->getGlobalSettings('advance_settings');
        return [
            'optimized_images' => Arr::get($global_settings, 'global_settings.optimized_images', 'false'),
            'has_gdpr' => Arr::get($gdpr_settings, 'has_gdpr', "false"),
            'image_format' => Arr::get($gdpr_settings, 'optimize_image_format', 'jpg')
        ];
    }
}
