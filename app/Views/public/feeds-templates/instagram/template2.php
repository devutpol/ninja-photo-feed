<?php
defined('ABSPATH') or die;

use NinjaPhotoFeed\Framework\Support\Arr;

if (!empty($feeds) && is_array($feeds)) {
    $npfeed_column      = isset($template_meta['column_number']) ? $template_meta['column_number'] : 4;
    $npfeed_column_class = 'npfeed-col-' . $npfeed_column;
    $npfeed_layout_type = isset($template_meta['layout_type']) && has_action('npfeed_loading_layout_' . $template_meta['layout_type']) ? $template_meta['layout_type'] : 'grid';
    $npfeed_image_aspect_ratio = Arr::get($template_meta, 'post_settings.aspect_ratio', 'classic');
    foreach ($feeds as $npfeed_index => $npfeed_feed) {
        if ($npfeed_index >= $sinceId && $npfeed_index <= $maxId) {
            $npfeed_feed_link = Arr::get($npfeed_feed, 'shoppable_options.show_shoppable') ? Arr::get($npfeed_feed, 'shoppable_options.url_settings.url', '') : Arr::get($npfeed_feed,'permalink', '');
            $npfeed_target = (Arr::get($template_meta, 'post_settings.display_mode', '') === 'instagram') ? '_blank' : '';
            $npfeed_display_optimize_image = Arr::get($image_settings, 'optimized_images', '');
            $npfeed_image_size = Arr::get($template_meta, 'post_settings.resolution', 'full');
            $npfeed_display_mode = $template_meta['post_settings']['display_mode'];
            $npfeed_user_name = Arr::get($npfeed_feed, 'username', '');
            $npfeed_feed_id = Arr::get($npfeed_feed, 'id', '');

            if (Arr::get($npfeed_feed, 'shoppable_options')) {
                $npfeed_target = Arr::get($npfeed_feed, 'shoppable_options.url_settings.open_in_new_tab') ? '_blank' : '';
            }
            if ($npfeed_layout_type === 'highlights' && has_action('npfeed_loading_layout_highlights')) {
                // Big/small placement is decided in CSS by :nth-child.
                echo '<div class="npfeed-highlight-item">';
            } elseif ($npfeed_layout_type !== 'carousel') {
                /**
                 * instagram_feed_template_item_wrapper_before hook.
                 *
                 * @hooked InstagramTemplateHandler::renderTemplateItemWrapper - 10 (outputs opening divs for the template item)
                 * */
                do_action('ninjaphotofeed/instagram_feed_template_item_wrapper_before', $template_meta);
            }
            ?>
            <div role="group" class="npfeed-ig-post npfeed-image-ratio-<?php echo esc_attr($npfeed_image_aspect_ratio); ?> <?php echo ($npfeed_layout_type === 'carousel' && has_action('npfeed_loading_layout_carousel')) ? 'swiper-slide' : ''; ?>" data-post_id="<?php echo esc_attr(Arr::get($npfeed_feed, 'id', ''));?>" data-user_name="<?php echo esc_attr(Arr::get($npfeed_feed, 'username', ''));?>" data-image_size="<?php echo esc_attr(Arr::get($template_meta, 'post_settings.resolution', 'full'));?>">
                <?php
                // Without an href the anchor is not focusable, so the caption stays keyboard-unreachable.
                $npfeed_has_link = ($npfeed_display_mode === 'instagram' && $npfeed_feed_link);
                ?>
                <a class="npfeed-ig-playmode" <?php echo $npfeed_has_link ? 'href="' . esc_url($npfeed_feed_link) . '"' : 'tabindex="0" role="button"'; ?>
                   target="<?php echo esc_attr($npfeed_target); ?>"
                   data-index="<?php echo esc_attr($npfeed_index); ?>"
                   data-playmode="<?php echo isset($npfeed_display_mode) ? esc_attr($npfeed_display_mode) : 'instagram'; ?>"
                   data-template-id="<?php echo esc_attr($templateId); ?>"
                   data-user_name="<?php echo esc_attr($npfeed_user_name);?>" data-post_id="<?php echo esc_attr($npfeed_feed_id);?>" 
                   data-image_size="<?php echo esc_attr($npfeed_image_size);?>"
                   data-optimized_images="<?php echo esc_attr($npfeed_display_optimize_image);?>"
                   rel="noopener noreferrer"
                >
                    <?php
                    /**
                     * instagram_post_media hook.
                     *
                     * @hooked InstagramTemplateHandler::renderPostMedia 10
                     * */
                    do_action('ninjaphotofeed/instagram_post_media', $npfeed_feed, $template_meta, $npfeed_index, $npfeed_display_optimize_image);
                    ?>
                </a>
                <?php
                /**
                 * instagram_post_author hook.
                 *
                 * @hooked InstagramTemplateHandler::renderPostAuthor 10
                 * */
                do_action('ninjaphotofeed/instagram_post_author', $npfeed_feed, $template_meta);
                ?>

                <?php
                    if(Arr::get($npfeed_feed, 'shoppable_options.show_shoppable') && Arr::get($template_meta, 'post_settings.display_mode') !== 'popup') {
                        do_action('ninjaphotofeed/instagram_shoppable_button', $npfeed_feed, $template_meta);
                    }
                ?>

                <?php if (count($npfeed_feed) > 6) { ?>
                    <div class="npfeed-ig-post-info">
                        <?php
                        /**
                         * instagram_post_statistics hook.
                         *
                         * @hooked render_instagram_statistics_html 10
                         * */
                        do_action('ninjaphotofeed/instagram_post_statistics', $npfeed_feed, $template_meta);

                        /**
                         * instagram_post_date hook.
                         *
                         * @hooked InstagramTemplateHandler::renderPostDate 10
                         * */
                        do_action('ninjaphotofeed/instagram_post_date', $npfeed_feed, $template_meta);

                        /**
                         * instagram_post_caption hook.
                         *
                         * @hooked InstagramTemplateHandler::renderPostCaption 10
                         * */
                        do_action('ninjaphotofeed/instagram_post_caption', $npfeed_feed, $template_meta);
                        ?>
                    </div>
                <?php } ?>
            </div>
            <?php if ($npfeed_layout_type !== 'carousel') { ?>
                </div>
            <?php } ?>
            <?php
        }
    }
}