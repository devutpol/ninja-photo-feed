<?php

namespace NinjaPhotoFeed\App\Services;

class PermissionManager
{
	public static function getReadablePermissions()
	{
		return apply_filters('ninjaphotofeed/readable_permissions', [
			'npfeed_full_access'         => [
				'title'   => __('Full Access', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_full_access'
			],
            'npfeed_manage_platforms'         => [
				'title'   => __('Manage Platforms', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_platforms'
			],
			'npfeed_manage_qr_codes'         => [
				'title'   => __('Manage QR Codes', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_qr_codes'
			],
			'npfeed_manage_reviews'         => [
				'title'   => __('Manage Reviews', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_reviews'
			],
			'npfeed_manage_testimonials'         => [
				'title'   => __('Manage Testimonials', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_testimonials'
			],
			'npfeed_manage_templates'        => [
				'title'   => __('Manage Templates', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_templates'
			],
			'npfeed_manage_notification_popup'        => [
				'title'   => __('Manage Notification Popup', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_notification_popup'
			],
			'npfeed_manage_chat_widgets'          => [
				'title'   => __('Manage Chat Widgets', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_manage_chat_widgets'
			],
			'npfeed_feeds_platforms_settings' => [
				'title'   => __('Manage Feeds Platforms Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_feeds_platforms_settings'
			],
			'npfeed_reviews_platforms_settings' => [
				'title'   => __('Manage Reviews Platforms Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_reviews_platforms_settings'
			],
			'npfeed_shoppable_settings' => [
				'title'   => __('Manage Shoppable Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_shoppable_settings'
			],
			'npfeed_translation_settings' => [
				'title'   => __('Manage Translation Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_translation_settings'
			],
			'npfeed_license_settings' => [
				'title'   => __('Manage License Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_license_settings'
			],
			'npfeed_feeds_advance_settings' => [
				'title'   => __('Manage Advanced Settings', 'ninja-photo-feed'),
				'depends' => [],
				'group'   => 'dashboard',
				'slug'    => 'npfeed_feeds_advance_settings'
			],
		]);
	}

	public static function pluginPermissions()
	{
		return apply_filters('ninjaphotofeed/plugin_permissions', [
            'npfeed_full_access',
			'npfeed_manage_platforms',
			'npfeed_manage_reviews',
			'npfeed_manage_testimonials',
			'npfeed_manage_templates',
			'npfeed_manage_notification_popup',
			'npfeed_manage_chat_widgets',
			'npfeed_feeds_platforms_settings',
			'npfeed_reviews_platforms_settings',
			'npfeed_shoppable_settings',
			'npfeed_translation_settings',
			'npfeed_license_settings',
            'npfeed_feeds_advance_settings',
			'npfeed_manage_qr_codes',
		]);
	}

	public static function attachPermissions($user, $permissions)
	{
		if (is_numeric($user)) {
			$user = get_user_by('ID', $user);
		}

		if (!$user) {
			return false;
		}

		if (user_can($user, 'manage_options')) {
			return $user;
		}

		$allPermissions = self::pluginPermissions();
		foreach ($allPermissions as $permission) {
			$user->remove_cap($permission);
		}

		$permissions = array_intersect($allPermissions, $permissions);

		foreach ($permissions as $permission) {
			$user->add_cap($permission);
		}

		return $user;
	}

	public static function getUserPermissions($user = false)
	{
		if (is_numeric($user)) {
			$user = get_user_by('ID', $user);
		}

		if (!$user) {
			return [];
		}

		$pluginPermission = self::pluginPermissions();

		if ($user->has_cap('manage_options')) {
			$pluginPermission[] = 'administrator';
			$permissions = $pluginPermission;
		} else {
			$permissions = array_values(array_intersect(array_keys($user->allcaps), $pluginPermission));
		}

		$permissions = apply_filters('ninjaphotofeed/user_permissions', $permissions, $user);
		return array_values($permissions);
	}

	public static function currentUserPermissions($cached = true)
	{
		static $permissions;

		if ($permissions && $cached) {
			return $permissions;
		}

		$permissions = self::getUserPermissions(get_current_user_id());

		return $permissions;
	}

	/**
	 * Drops the memoised permission set. currentUserPermissions() caches per process,
	 * so anything that switches the current user mid-process must call this.
	 */
	public static function refreshCurrentUserPermissions()
	{
		return self::currentUserPermissions(false);
	}

	public static function currentUserCan($permission)
	{
		if (current_user_can('manage_options')) {
			return true;
		}

		$permissions = self::currentUserPermissions();

		if (in_array('npfeed_full_access', $permissions)) {
			return true;
		}

		return in_array($permission, $permissions);
	}

	/**
	 * Each managed post type keeps the capability its own screen requires. Not filtered:
	 * widening this map would let one screen's capability reach another screen's objects.
	 */
	public static function managedPostTypeCapabilities()
	{
		return [
			'npfeed_template'   => 'npfeed_manage_templates',
			'npfeed_reviews_notify' => 'npfeed_manage_notification_popup',
			'npfeed_social_chats'   => 'npfeed_manage_chat_widgets',
			'npfeed_custom_source'  => 'npfeed_full_access',
		];
	}

	/**
	 * True when the current user holds the capability owning this post type.
	 * Unmanaged and empty post types are always refused.
	 */
	public static function currentUserCanManagePostType($postType)
	{
		$capabilities = self::managedPostTypeCapabilities();

		if (!$postType || !isset($capabilities[$postType])) {
			return false;
		}

		return self::currentUserCan($capabilities[$postType]);
	}

	/**
	 * True when the user holds any plugin capability. For endpoints behind shared,
	 * non-feature UI such as the global dashboard notices.
	 */
	public static function currentUserCanAny()
	{
		if (current_user_can('manage_options')) {
			return true;
		}

		return !empty(self::currentUserPermissions());
	}
}
