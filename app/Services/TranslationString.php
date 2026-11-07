<?php


namespace NinjaPhotoFeed\App\Services;

use NinjaPhotoFeed\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

class TranslationString
{
    public static function getStrings()
    {
        $translations = GlobalSettings::getTranslations();

        $i18n = array(
            'Subscribers' => Arr::get($translations, 'subscribers') ?: __('Subscribers', 'ninja-photo-feed'),
            'Following' => Arr::get($translations, 'following') ?: __('Following', 'ninja-photo-feed'),
            'Followers' => Arr::get($translations, 'followers') ?: __('Followers', 'ninja-photo-feed'),
            'Videos' => Arr::get($translations, 'videos') ?: __('Videos', 'ninja-photo-feed'),
            'Views' => Arr::get($translations, 'views') ?: __('Views', 'ninja-photo-feed'),
            'Tweets' => Arr::get($translations, 'tweets') ?: __('Tweets', 'ninja-photo-feed'),
            'Posts' => Arr::get($translations, 'posts') ?: __('Posts', 'ninja-photo-feed'),
            'Where you want to leave a review' => Arr::get($translations, 'leave_a_review') ?: __('Where you want to leave a review', 'ninja-photo-feed'),
            'Recommends' => Arr::get($translations, 'recommends') ?: __('Recommends', 'ninja-photo-feed'),
            'Does not recommend' => Arr::get($translations, 'does_not_recommend') ?: __('Does not recommend', 'ninja-photo-feed'),
            'On' => Arr::get($translations, 'on') ?: __('On', 'ninja-photo-feed'),
            'Read all reviews' => Arr::get($translations, 'read_all_reviews') ?: __('Read all reviews', 'ninja-photo-feed'),
            'Read More' => Arr::get($translations, 'read_more') ?: __('Read More', 'ninja-photo-feed'),
            'Read Less' => Arr::get($translations, 'read_less') ?: __('Read Less', 'ninja-photo-feed'),
            'Comments' => Arr::get($translations, 'comments') ?: __('Comments', 'ninja-photo-feed'),
            'View on Facebook' => Arr::get($translations, 'view_on_fb') ?: __('View on Facebook', 'ninja-photo-feed'),
            'View on Instagram' => Arr::get($translations, 'view_on_ig') ?: __('View on Instagram', 'ninja-photo-feed'),
            'View on TikTok' => Arr::get($translations, 'view_on_tiktok') ?: __('View on TikTok', 'ninja-photo-feed'),
            'Likes' => Arr::get($translations, 'likes') ?: __('Likes', 'ninja-photo-feed'),
            'People Responded' => Arr::get($translations, 'people_responded') ?: __('People Responded', 'ninja-photo-feed'),
            'Online Event' => Arr::get($translations, 'online_event') ?: __('Online Event', 'ninja-photo-feed'),
            'Interested' => Arr::get($translations, 'interested') ?: __('Interested', 'ninja-photo-feed'),
            'Going' => Arr::get($translations, 'going') ?: __('Going', 'ninja-photo-feed'),
            'Went' => Arr::get($translations, 'went') ?: __('Went', 'ninja-photo-feed'),
            'AI-Generated Summary' => Arr::get($translations, 'ai_generated_summary') ?: __('AI-Generated Summary', 'ninja-photo-feed'),
            'All Platforms' => __('All Platforms', 'ninja-photo-feed'),
            'Social Feeds' => __('Social Feeds', 'ninja-photo-feed'),
            'Business Reviews' => __('Business Reviews', 'ninja-photo-feed'),
            'Social Chats' => __('Social Chats', 'ninja-photo-feed'),
            'Search Platforms' => __('Search Platforms', 'ninja-photo-feed'),
            'Screen Options' => __('Screen Options', 'ninja-photo-feed'),
            'Enabled' => __('Enabled', 'ninja-photo-feed'),
            'Disabled' => __('Disabled', 'ninja-photo-feed'),
            'All Enabled' => __('All Enabled', 'ninja-photo-feed'),
            'None Enabled' => __('None Enabled', 'ninja-photo-feed'),
            'Some Enabled' => __('Some Enabled', 'ninja-photo-feed'),
            'Error Logs' => __('Error Logs', 'ninja-photo-feed'),
            'Reset Error Logs' => __('Reset Error Logs', 'ninja-photo-feed'),
            'See All' => __('See All', 'ninja-photo-feed'),
            'Would you like to clear all the errors stored in the error log' => __('Would you like to clear all the errors stored in the error log', 'ninja-photo-feed'),
            'Select Bubble Icon' => __('Select Bubble Icon', 'ninja-photo-feed')
        );

        // Merge auto-extracted admin strings first (lower priority),
        // then overlay with user-configurable strings from $i18n above.
        $merged = array_merge(TranslationStrings::getAdminStrings(), $i18n);

        return apply_filters('ninjaphotofeed/translation_strings_i18n', $merged);
    }

}