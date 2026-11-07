<?php
defined('ABSPATH') or die;

use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\Helper as InstagramHelper;

//carousel
$npfeed_data_attrs  = array();
$npfeed_slider_data = array();
if ($layout_type === 'carousel') {
    $npfeed_slider_data = array(
        'autoplay'               => $feed_settings['carousel_settings']['autoplay'],
        'autoplay_speed'         => $feed_settings['carousel_settings']['autoplay_speed'],
        'spaceBetween'           => Arr::get($feed_settings, 'carousel_settings.spaceBetween'),
        'responsive_slides_to_show'  => Arr::get($feed_settings, 'carousel_settings.responsive_slides_to_show'),
        'responsive_slides_to_scroll'  => Arr::get($feed_settings, 'carousel_settings.responsive_slides_to_scroll'),
        'navigation'             => $feed_settings['carousel_settings']['navigation'],
    );
}
$npfeed_data_attrs[] = $layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel') ? 'data-slider_settings=' . json_encode($npfeed_slider_data) . '' : '';

// wrapper classes
$npfeed_classes   = array('npfeed-ig-feed-wrapper', 'npfeed-feed-wrap', 'npfeed_content');
$npfeed_classes[] = InstagramHelper::getTemplateClass($template);
$npfeed_classes[] = 'npfeed-ig-feed-template-' . esc_attr($templateId) . '';
$npfeed_classes[] = $layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel') ? 'npfeed-instagram-slider-activate' : '';
$npfeed_classes[] = $layout_type === 'masonry' ? 'npfeed-instagram-masonry-activate' : '';
$npfeed_classes[] = $layout_type === 'list' ? 'npfeed-instagram-list-activate' : '';
$npfeed_classes[] = InstagramHelper::getColorSchemeClass($feed_settings);
$npfeed_desktop_column_number   = Arr::get($feed_settings, 'responsive_column_number.desktop');

$npfeed_header_settings = $feed_settings['header_settings'];

$npfeed_ig_header_layout_class = InstagramHelper::getHeaderLayoutClass($feed_settings);

echo '<div id="npfeed-ig-feed-' . esc_attr($templateId) . '" class="' . esc_attr(implode(' ', $npfeed_classes)) . '" ' . esc_attr(implode(' ',
        $npfeed_data_attrs)) . '  data-column="' . esc_attr($npfeed_desktop_column_number) . '">';
echo '<div class="npfeed-container npfeed-insta-feed-' . esc_attr($templateId) . '">';
?>

<?php if ($npfeed_header_settings['display_header'] === 'true'): ?>
    <div class="npfeed-ig-header <?php echo esc_attr($npfeed_ig_header_layout_class); ?> npfeed-gap-<?php echo esc_attr($column_gaps); ?>">
        <div class="npfeed-ig-header-inner">
            <?php do_action('ninjaphotofeed/instagram_user_avatar', $header, $npfeed_header_settings); ?>

            <div class="npfeed-ig-header-info">
                <?php if ($npfeed_header_settings['display_username'] === 'true' && Arr::get($header, 'username')) { ?>
                    <div class="npfeed-ig-header-name">
                        <a target="_blank"
                           rel="noopener noreferrer"
                           href="<?php echo esc_url('https://www.instagram.com/' . $header['username']); ?>"
                           title="<?php echo esc_attr($header['username']); ?>">
                            @<?php echo esc_html($header['username']); ?>
                        </a>
                    </div>
                <?php } ?>
                <?php
                /**
                 * instagram_header_statistics hook.
                 *
                 * @hooked render_instagram_header_statistics_html 10
                 * */
                do_action('ninjaphotofeed/instagram_header_statistics', $header, $npfeed_header_settings, $translations);
                ?>
                <?php if (Arr::get($header, 'name') && $npfeed_header_settings['display_name'] === 'true') { ?>
                    <h3 class="npfeed-ig-header-fullname">
                        <?php echo esc_html($header['name']); ?>
                    </h3>
                <?php } ?>
                <?php if ($npfeed_header_settings['display_description'] === 'true') { ?>
                    <div class="npfeed-ig-header-description">
                        <?php if (!empty($npfeed_header_settings['custom_profile_bio_text'])) { ?>
                            <p> <?php echo wp_kses_post(nl2br($npfeed_header_settings['custom_profile_bio_text'])); ?></p>
                        <?php } ?>
                        <?php if (empty($npfeed_header_settings['custom_profile_bio_text']) && Arr::get($header,
                                'biography')) { ?>
                            <p><?php echo wp_kses_post(nl2br($header['biography'])); ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <?php
            /**
             * instagram_follow_button hook.
             *
             * @hooked render_instagram_follow_button_html 10
             * */
            if (Arr::get($feed_settings, 'follow_button_settings.follow_button_position') !== 'footer') {
                do_action('ninjaphotofeed/instagram_follow_button', $feed_settings);
            }
            ?>
        </div>
    </div>
<?php endif;

echo '<div class="npfeed-ig-feed-wrapper-inner">';
if($layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel')) {
    echo '<div class="swiper-container" tabindex="0">';
}
if ($layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel')) {
    $npfeed_row_classes = 'swiper-wrapper';
} elseif ($layout_type === 'highlights' && has_action('npfeed_loading_layout_highlights')) {
    $npfeed_row_classes = 'npfeed-ig-highlights-grid';
} else {
    $npfeed_row_classes = 'npfeed-row';
}

echo '<div class="'.esc_attr($npfeed_row_classes).' npfeed-ig-all-feed npfeed_feeds npfeed-column-gap-' . esc_attr($column_gaps) . '">';
