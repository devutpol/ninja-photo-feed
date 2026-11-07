<?php

namespace NinjaPhotoFeed\App\Services\Platforms\Feeds;

use NinjaPhotoFeed\App\Services\GlobalSettings;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\Helper as InstagramHelper;

use NinjaPhotoFeed\Framework\Support\Arr;

/**
 * Instagram-only version of core's Config. The other platforms' format*Config()
 * methods are dropped: this build ships no class that still calls them.
 */
class Config
{
    /**
     * Formatted All Instagram Configs
     *
     * @param array $settings
     * @param array $response
     *
     * @return array
     * @since 1.3.0
     */
    public static function formatInstagramConfig($settings, $response)
    {
        $accounts    = InstagramHelper::getUserAccounts($settings);
        $displayMode = Arr::get($settings, 'post_settings.display_mode', 'instagram');
        // This build ships no popup, so a saved popup mode opens the post on Instagram.
        $displayMode = ($displayMode === 'popup') ? 'instagram' : $displayMode;

        $header_layout = Arr::get($settings, 'header_settings.header_layout', 'classic');
        if (!in_array($header_layout, array('classic', 'minimal', 'inline'), true)) {
            $header_layout = 'classic';
        }

        return array(
            'feed_settings' => array (
                'platform'      => 'instagram',
                'created_from_onboarding' => Arr::get($settings,'created_from_onboarding', false),
                'template'      => Arr::get($settings,'template', 'template1'),
                'layout_type'   => Arr::get($settings,'layout_type', 'grid'),
                'column_number' => Arr::get($settings,'column_number', '4'),
                'responsive_column_number'  => array(
                    'desktop'  => Arr::get($settings, 'responsive_column_number.desktop', Arr::get($settings,'column_number', '4')),
                    'tablet'   => Arr::get($settings, 'responsive_column_number.tablet','4'),
                    'mobile'   => Arr::get($settings, 'responsive_column_number.mobile', '12')
                ),
                'column_gaps'   => Arr::get($settings,'column_gaps', 'default'),
                'color_scheme'  => InstagramHelper::sanitizeColorScheme(Arr::get($settings,'color_scheme', 'light')),
                'enable_style'  => 'true',

                'source_settings' => array(
                    'feed_type'    => Arr::get($settings,'source_settings.feed_type', 'user_account_feed'),
                    'account_ids'  => $accounts['account_ids'],
                    'hash_tags'    => sanitize_text_field(Arr::get($settings,'source_settings.hash_tags', '')),
                    'hashtag_type' => Arr::get($settings,'source_settings.hashtag_type', 'top_media'),
                ),

                'carousel_settings' => array(
                    'autoplay'         => Arr::get($settings,'carousel_settings.autoplay', 'true'),
                    'autoplay_speed'   => (int) Arr::get($settings,'carousel_settings.autoplay_speed', 3000),
                    'slides_to_show'   => (int) Arr::get($settings,'carousel_settings.slides_to_show', 3),
                    'spaceBetween'     => (int) Arr::get($settings,'carousel_settings.spaceBetween', 20),
                    'responsive_slides_to_show'  => array(
	                    'desktop'  => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.desktop', Arr::get($settings, 'carousel_settings.slides_to_show', 3)),
	                    'tablet'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.tablet',2),
	                    'mobile'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_show.mobile', 1)
                    ),
                    'slides_to_scroll' => (int) Arr::get($settings,'carousel_settings.slides_to_scroll', 3),
                    'responsive_slides_to_scroll' => array(
	                    'desktop'  => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.desktop', Arr::get($settings, 'carousel_settings.slides_to_scroll', 3)),
	                    'tablet'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.tablet',2),
	                    'mobile'   => (int)Arr::get($settings, 'carousel_settings.responsive_slides_to_scroll.mobile', 1)
                    ),
                    'navigation'       => Arr::get($settings,'carousel_settings.navigation', 'dot')
                ),

                'header_settings' => array(
                    'display_header'            => Arr::get($settings,'header_settings.display_header', 'true'),
                    'header_layout'             => $header_layout,
                    'account_to_show'           => $accounts['connected_account_id'],
                    'display_name'              => Arr::get($settings,'header_settings.display_name', 'true'),
                    'display_username'          => Arr::get($settings,'header_settings.display_username', 'true'),
                    'display_avatar'            => Arr::get($settings,'header_settings.display_avatar', 'true'),
                    'display_description'       => Arr::get($settings,'header_settings.display_description', 'true'),
                    'display_posts_counter'     => Arr::get($settings,'header_settings.display_posts_counter', 'true'),
                    'display_followers_counter' => Arr::get($settings,'header_settings.display_followers_counter', 'true'),
                    'custom_profile_bio_text'   => sanitize_text_field(Arr::get($settings,'header_settings.custom_profile_bio_text', '')),
                    'custom_profile_photo'      => Arr::get($settings,'header_settings.custom_profile_photo', ''),
                ),

                'post_settings' => array (
                    'resolution'               => Arr::get($settings,'post_settings.resolution', 'full'),
                    'aspect_ratio'             => Arr::get($settings,'post_settings.aspect_ratio', 'frame45'),
                    'display_mode'             => $displayMode,
                    'display_likes_counter'    => Arr::get($settings,'post_settings.display_likes_counter', 'true'),
                    'display_comments_counter' => Arr::get($settings,'post_settings.display_comments_counter', 'true'),
                    'display_caption'          => Arr::get($settings,'post_settings.display_caption', 'true'),
                    'trim_caption_words'       => (int) Arr::get($settings,'post_settings.trim_caption_words', 15),
                    'display_date'             => Arr::get($settings,'post_settings.display_date', 'true') === 'false' ? 'false' : 'true',
                ),

                'popup_settings' => array (
                    'display_sidebar'       => Arr::get($settings,'popup_settings.display_sidebar', 'true'),
                    'display_profile_photo' => Arr::get($settings,'popup_settings.display_profile_photo', 'true'),
                    'display_username'      => Arr::get($settings,'popup_settings.display_username', 'true'),
                    'display_caption'       => Arr::get($settings,'popup_settings.display_caption', 'true'),
                    'display_date'          => Arr::get($settings,'popup_settings.display_date', 'true'),
                    'display_comments'      => Arr::get($settings,'popup_settings.display_comments', 'true'),
                    'display_cta_btn'       => Arr::get($settings,'popup_settings.display_cta_btn', 'true'),
                    'display_next_prev_arrows'  => Arr::get($settings,'popup_settings.display_next_prev_arrows', 'true'),
                ),

                'follow_button_settings' => array (
                    'display_follow_button'  => Arr::get($settings,'follow_button_settings.display_follow_button', 'true'),
                    'follow_button_text'     => sanitize_text_field(Arr::get($settings,'follow_button_settings.follow_button_text', __('Follow on Instagram', 'ninja-photo-feed'))),
                    'follow_button_position' => Arr::get($settings,'follow_button_settings.follow_button_position', 'header'),
                ),

                'filters' => array (
                    'total_posts'      => (int) Arr::get($settings,'filters.total_posts', 50),
                    'total_posts_number'  => array(
                        'desktop'  => (int) Arr::get($settings, 'filters.total_posts_number.desktop', Arr::get($settings,'filters.total_posts', 50)),
                        'mobile'   => (int) Arr::get($settings, 'filters.total_posts_number.mobile', Arr::get($settings,'filters.total_posts', 50))
                    ),
                    'post_order'       => Arr::get($settings,'filters.post_order', 'ascending'),
                    'post_type'        => Arr::get($settings,'filters.post_type', 'all'),
                    'includes_inputs'  => sanitize_text_field(Arr::get($settings,'filters.includes_inputs', '')),
                    'excludes_inputs'  => sanitize_text_field(Arr::get($settings,'filters.excludes_inputs', '')),
                    'hide_posts_by_id' => sanitize_text_field(Arr::get($settings,'filters.hide_posts_by_id', ''))
                ),

                'pagination_settings' => array (
                    'pagination_type'       => Arr::get($settings,'pagination_settings.pagination_type', 'none'),
                    'load_more_button_text' => sanitize_text_field(Arr::get($settings, 'pagination_settings.load_more_button_text', __('Load More', 'ninja-photo-feed'))),
                    'paginate'              => (int) Arr::get($settings,'pagination_settings.paginate', 6),
                    'paginate_number'       => array(
                        'desktop' => (int) Arr::get($settings, 'pagination_settings.paginate_number.desktop', Arr::get($settings, 'pagination_settings.paginate', 6)),
                        'mobile'  => (int) Arr::get($settings, 'pagination_settings.paginate_number.mobile', 6)
                    ),
                ),

                'shoppable_settings' => array(
                    'enable_shoppable'      => Arr::get($settings,'shoppable_settings.enable_shoppable', 'false'),
                    'include_shoppable_by_hashtags'      => Arr::get($settings,'shoppable_settings.include_shoppable_by_hashtags', 'false'),
                    'display_shoppable_icon'    => Arr::get($settings,'shoppable_settings.display_shoppable_icon', 'false'),
                    'shoppable_feeds'           => Arr::get($settings,'shoppable_settings.shoppable_feeds', []),
                )
            ),
            'dynamic'       => $response
        );
    }
}
