<?php

defined('ABSPATH') or die;

/**
 * Ninja Photo Feed - Instagram Actions
 *
 * All registered action handlers for Instagram feed functionality.
 */

/**
 * @var $app \NinjaPhotoFeed\Framework\Foundation\Application
 */

// Init the platform on plugin load
(new \NinjaPhotoFeed\App\Hooks\Handlers\PlatformHandler())->register();

$app->addAction('admin_menu', 'AdminMenuHandler@addMenus');
$app->addAction('admin_enqueue_scripts', 'AdminMenuHandler@enqueueAssets');
$app->addAction('admin_init', function () {
    $disablePages = [
        'ninjaphotofeed.php',
    ];
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking for admin page context, not processing form data
    if (isset($_GET['page']) && in_array($_GET['page'], $disablePages)) {
        remove_all_actions('admin_notices');
    }
});

// Lets site owners include this in their own privacy policy; see readme.txt's "External services".
add_action('admin_init', function () {
    if (!function_exists('wp_add_privacy_policy_content')) {
        return;
    }

    wp_add_privacy_policy_content(
        __('Ninja Photo Feed', 'ninja-photo-feed'),
        wp_kses_post(
            '<p>' . __('When an administrator connects an Instagram account, this site sends your site\'s admin URL, one-time login codes, and the connecting administrator\'s email address and display name to the WP Social Ninja connect service (connect.wpsocialninja.com), which stores the email address only if the administrator opts in to getting-started emails. The connect service does not store access tokens or IP addresses. Access tokens are renewed by this site directly with Instagram, not through the connect service. This site also sends the connected account\'s access token and user ID to the Instagram/Meta Graph API to fetch posts, profile information, and renew the access token.', 'ninja-photo-feed') . '</p>' .
            '<p>' . __('Unless "Optimize Images" is turned on under Ninja Photo Feed\'s Global Feeds Settings, visitors\' browsers load feed images directly from Instagram\'s servers, which receives the visitor\'s IP address and browser information.', 'ninja-photo-feed') . '</p>'
        )
    );
});

add_action('save_post', function ($postId, $post) use ($app) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Ensure $post is valid
    if (!is_a($post, 'WP_Post') || !isset($post->post_content)) {
        return;
    }

    $ids = \NinjaPhotoFeed\App\Services\Helper::getShortCodeIds($post->post_content);

    if ($ids) {
        $shortcodeIds = array_values($ids);
        $templates = $app->db->table('posts')
            ->select(['post_content'])
            ->whereIn('ID', $shortcodeIds)
            ->where('post_type', 'npfeed_template')
            ->get();
        $items = [];
        foreach ($templates as $template) {
            $platform = $template->post_content;
            if (!$platform) {
                continue;
            }
            if (defined('FL_BUILDER_VERSION')) {
                $platform = str_replace('empty_', '', $platform);
            }
            $platform = substr($platform, 0, 20);
            $items[$platform] = $platform;
        }
        $items = array_values($items);
        update_post_meta($postId, '_npfeed_ids', $items);
    } else {
        delete_post_meta($postId, '_npfeed_ids');
    }
}, 10, 2);

add_action('init', function () {
    // Ensure cron jobs are registered on plugin load
    (new \NinjaPhotoFeed\App\Hooks\Handlers\ActivateCronEvent())->activate();
    (new \NinjaPhotoFeed\App\Hooks\Handlers\ShortcodeHandler())->addShortcode();
});

/*
 * Common Ajax Hook for Load More Feeds
 */
$app->addAction('wp_ajax_npfeed_get_more_feeds', 'ShortcodeHandler@handleLoadMoreAjax');
$app->addAction('wp_ajax_nopriv_npfeed_get_more_feeds', 'ShortcodeHandler@handleLoadMoreAjax');

/**
 * Gutenberg blocks register
 */
add_action('enqueue_block_editor_assets', function () {
    // wp-block-editor is required for useBlockProps (Block API v3 / iframe editor support).
    $dependencies = array('wp-blocks', 'wp-i18n', 'wp-polyfill', 'wp-element', 'wp-components', 'wp-block-editor');

    if (version_compare(get_bloginfo('version'), '5.8', '<')) {
        $dependencies[] = 'wp-editor';
    }

    wp_enqueue_script(
        'npfeed-gutenberg-block',
        NINJAPHOTOFEED_URL . 'assets/js/npfeed-shortcode-block.js',
        $dependencies,
        NINJAPHOTOFEED_VERSION,
        true
    );

    $defaults = array(
        'numberposts' => -1,
        'orderby' => 'id',
        'order' => 'DESC',
        'post_type' => 'npfeed_template',
    );

    $templates = get_posts($defaults);
    $formatted = array();
    foreach ($templates as $template) {
        $formatted[] = array(
            'id' => $template->ID,
            'title' => $template->post_title . ' (' . $template->ID . ')',
            'platform' => 'instagram'
        );
    }

    array_unshift($formatted, (object)[
        'id' => '0',
        'platform' => 'instagram',
        'title' => __('-- Select a template --', 'ninja-photo-feed')
    ]);

    wp_localize_script('npfeed-gutenberg-block', 'npfeed_block_vars', [
        'logo' => NINJAPHOTOFEED_URL . 'assets/images/logo/ninja-photo-feed.svg',
        'templates' => $formatted
    ]);

    wp_enqueue_style(
        'npfeed-gutenberg-block',
        NINJAPHOTOFEED_URL . 'assets/css/ninja-photo-feed-gutenblock.css',
        array('wp-edit-blocks'),
        NINJAPHOTOFEED_VERSION
    );
});

/*******
 *
 * Instagram feed templates action hooks
 *
 *******/
$app->addAction('ninjaphotofeed/instagram_feed_template_item_wrapper_before', 'InstagramTemplateHandler@renderTemplateItemWrapper');
$app->addAction('ninjaphotofeed/instagram_post_media', 'InstagramTemplateHandler@renderPostMedia', 10, 4);
$app->addAction('ninjaphotofeed/instagram_post_caption', 'InstagramTemplateHandler@renderPostCaption', 10, 2);
$app->addAction('ninjaphotofeed/instagram_icon', 'InstagramTemplateHandler@renderIcon');
$app->addAction('ninjaphotofeed/instagram_user_avatar', 'InstagramTemplateHandler@renderUserAvatar', 10, 2);
$app->addAction('ninjaphotofeed/instagram_header_statistics', 'InstagramTemplateHandler@renderHeaderStatisticsHtml', 10, 3);
$app->addAction('ninjaphotofeed/instagram_post_date', 'InstagramTemplateHandler@renderPostDate', 10, 2);
$app->addAction('ninjaphotofeed/instagram_follow_button', 'InstagramTemplateHandler@renderFollowButtonHtml');
$app->addAction('ninjaphotofeed/instagram_post_author', 'InstagramTemplateHandler@renderPostAuthor', 10, 2);

$app->addAction('ninjaphotofeed/load_more_button', 'InstagramTemplateHandler@renderLoadMoreButton', 10, 7);

/*
 * Cron Job Init
 */
$app->addAction('npfeed_cron_job', 'SchedulerHandler@handle');
$app->addAction('npfeed_scheduled_weekly', 'SchedulerHandler@processWeekly');

/*
 * Elementor Widget Init
 */
if (defined('ELEMENTOR_VERSION')) {
    new \NinjaPhotoFeed\App\Services\Widgets\ElementorWidget();
}

if (defined('LSCWP_V')) {
    add_action('litespeed_init', function () {
        if (isset($_SERVER['REQUEST_URI']) && strpos(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), 'ninjaphotofeed') !== false) {
            defined('LITESPEED_ESI_OFF') || define('LITESPEED_ESI_OFF', true);
        }
    });
}
