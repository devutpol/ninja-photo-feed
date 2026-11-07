<?php
defined('ABSPATH') or die;

use NinjaPhotoFeed\Framework\Support\Arr;

echo '</div>'; // row end
if( $layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel')) {
    echo '</div>'; // swiper container end
    echo '<div class="npfeed-swiper-carousel-wrapper">';
        if( $feed_settings['carousel_settings']['navigation'] === 'arrow' || $feed_settings['carousel_settings']['navigation'] === 'both') {
            echo '<div class="npfeed-swiper-prev-next npfeed-swiper-next swiper-button-next"></div>
              <div class="npfeed-swiper-prev-next npfeed-swiper-prev swiper-button-prev"></div>';
        }
        if( $feed_settings['carousel_settings']['navigation'] === 'dot' || $feed_settings['carousel_settings']['navigation'] === 'both') {
            echo '<div class="npfeed-swiper-pagination swiper-pagination"></div>';
        }
    echo '</div>';
}

$npfeed_mt_30 = $column_gaps === 'no_gap' ? 'npfeed-mt-20' : '';

echo '<div class="npfeed-ig-footer npfeed-row ' . esc_attr($npfeed_mt_30) . '">';
//pagination
if (count($feeds) > $paginate && $layout_type !== 'carousel'
    && $pagination_type === 'load_more'
    && has_filter('npfeed_feed_items_by_page_instagram')) {

    $npfeed_load_more_button_text = $feed_settings['pagination_settings']['load_more_button_text'];
    echo '<button aria-label="'.esc_attr($npfeed_load_more_button_text).'" class="npfeed-ig-load-more npfeed_more npfeed-load-more-default"
        id="npfeed-ig-load-more-btn-' . esc_attr($templateId) . '"
        data-paginate="' . intval($paginate) . '"
        data-template_id="' . intval($templateId) . '"
        data-template_type="' . esc_attr($layout_type) . '"
        data-platform="instagram"
        data-page="1"
        data-total="' . intval($total) . '">
        ' . esc_html($npfeed_load_more_button_text) . '
    <div class="npfeed-load-icon-wrapper"><span></span></div>
    </button>';
}

if (Arr::get($feed_settings, 'follow_button_settings.follow_button_position') !== 'header') {
    /**
     * instagram_follow_button hook.
     *
     * @hooked render_instagram_follow_button_html 10
     * */
    do_action('ninjaphotofeed/instagram_follow_button', $feed_settings);
}
echo '</div>';
echo '</div>'; // npfeed-ig-feed-wrapper-inner end

echo '</div>'; // npfeed-container end
echo '</div>'; // npfeed-yt-feed-wrapper end