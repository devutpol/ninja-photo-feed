<?php

namespace NinjaPhotoFeed\App\Services\Onboarding;

use NinjaPhotoFeed\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

class OnboardingHelper
{
    /**
     * Apply onboarding settings to a template's configuration array.
     *
     * Simplified version for Instagram-only plugin.
     *
     * @param int|null $templateId The template ID to get specific onboarding data
     * @param string $platform The platform identifier (e.g., 'instagram').
     * @param array $settings The template settings array to be modified.
     * @param array $business_info A reference to the business_info array.
     * @param array $filtered_reviews A reference to the filtered_reviews array.
     *
     * @return array The modified settings array.
     */
    public static function applyOnboardingSettings($templateId, $platform, &$settings, &$business_info = [], &$filtered_reviews = [], &$all_reviews = [])
    {
        // Check if the template was created from the onboarding wizard
        $is_onboarding = false;
        if (isset($settings['feed_settings'])) {
            $is_onboarding = Arr::get($settings, 'feed_settings.created_from_onboarding', false);

            // Get template ID from settings if not provided
            if (!$templateId) {
                $templateId = Arr::get($settings, 'template_id');
            }
        }

        if (!$is_onboarding || !$templateId) {
            return $settings;
        }

        // Get template-specific onboarding data from sessions
        $allSessions = get_option('npfeed_onboarding_sessions', []);
        $onboardingStatus = Arr::get($allSessions, $templateId);

        if (empty($onboardingStatus)) {
            return $settings;
        }

        $postType         = Arr::get($onboardingStatus, 'data.post_type');
        $templateName     = Arr::get($onboardingStatus, 'data.template.name');
        $layoutType       = Arr::get($onboardingStatus, 'data.template.type', 'grid');

        // Set template and layout configuration for Instagram
        if (isset($settings['feed_settings'])) {
            $settings['feed_settings']['source_settings']['feed_type'] = $postType;
            $settings['feed_settings']['template'] = $templateName;
            $settings['feed_settings']['layout_type'] = $layoutType;

            if ($platform === 'instagram' && $postType === 'shoppable') {
                $settings['feed_settings']['source_settings']['feed_type'] = 'user_account_feed';
                $settings['feed_settings']['shoppable_settings']['enable_shoppable'] = 'true';
                $settings['feed_settings']['shoppable_settings']['include_shoppable_by_hashtags'] = 'false';
            }
        }

        return $settings;
    }

    /**
     * Remove template from onboarding sessions when it's edited
     *
     * @param int $templateId The template ID
     * @return void
     */
    public static function removeFromOnboardingSessions($templateId)
    {
        $allSessions = get_option('npfeed_onboarding_sessions', []);

        if (isset($allSessions[$templateId])) {
            unset($allSessions[$templateId]);
            update_option('npfeed_onboarding_sessions', $allSessions);
        }
    }
}
