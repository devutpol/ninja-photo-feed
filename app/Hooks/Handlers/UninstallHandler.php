<?php
namespace NinjaPhotoFeed\App\Hooks\Handlers;
use NinjaPhotoFeed\Framework\Support\Arr;

class UninstallHandler
{
    public $isTableDelete = true;

    /**
     * Removes all plugin data unless the user opted to keep it (the default).
     * The guard lives here, not in deleteAllPlatformsData(), because the
     * "Delete All Data" button calls that method and must ignore the preference.
     *
     * @return void
     */
    public function handle()
    {
        $settings = get_option('npfeed_global_settings', []);
        $advanceSettings = Arr::get($settings, 'global_settings.advance_settings', []);
        if (Arr::get($advanceSettings, 'preserve_plugin_data', 'true') === 'true') {
            return;
        }

        $this->deleteAllPlatformsData($this->isTableDelete);
    }

    public function deleteAllPlatformsData($isDeleteTable)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $optionsToDelete = [
            'npfeed_instagram_verification_configs',
            'npfeed_instagram_global_settings',
            'npfeed_available_valid_platforms',
            'npfeed_onboarding_sessions',
            'npfeed_global_shoppable_settings',
            'npfeed_instagram_authorized_business_accounts_list',
            'npfeed_instagram_revoke_platform_data',
            'npfeed_instagram_failed_oembed_requests',
            'npfeed_instagram_oembed_permissions_errors',
            'npfeed_instagram_local_avatars',
            'npfeed_instagram_local_covers',
            'npfeed_auth_bridge_identity',
            'npfeed_statuses',
            'npfeed_errors',
        ];

        // Install state: removed only with the tables, which these flags describe.
        if ($isDeleteTable) {
            $optionsToDelete[] = '_ninja_photo_feed_version';
            $optionsToDelete[] = 'npfeed_optimize_images_table_status';
            $optionsToDelete[] = 'npfeed_youtube_privacy_cache_purged';
        }

        foreach ($optionsToDelete as $option) {
            delete_option($option);
        }

        global $wpdb;
        $tablesToDelete = [
            'npfeed_caches',
            'npfeed_optimize_images'
        ];

        foreach ($tablesToDelete as $table) {
            $tableName = $wpdb->prefix . $table;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Existence check before cleanup
            if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($tableName))) !== $tableName) {
                continue;
            }
            $tableName = esc_sql($tableName);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Required for plugin uninstall cleanup, table name is sanitized
            $wpdb->query("DELETE FROM `{$tableName}`;");
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for plugin uninstall cleanup
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s;", '%_npfeed_template_config%'));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for plugin uninstall cleanup
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->posts} WHERE post_type LIKE %s OR post_type = %s;", 'npfeed_%', 'npfeed_template'));

        if ($isDeleteTable) {
            foreach ($tablesToDelete as $table) {
                $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}{$table}`;"); // phpcs:ignore
            }
        }
    }
}
