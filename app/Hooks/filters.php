<?php

defined('ABSPATH') or die;

/**
 * Ninja Photo Feed - Instagram Filters
 *
 * All registered filter handlers for Instagram feed functionality.
 */

/**
 * @var $app \NinjaPhotoFeed\Framework\Foundation\Application
 */

add_filter('cron_schedules', function ($schedules) {
    // Adds custom schedules to the existing schedules.

    if (!isset($schedules['5min'])) {
        $schedules['5min'] = array(
            'display' => __('5 min', 'ninja-photo-feed'),
            'interval' => 300,
        );
    }

    if (!isset($schedules['2days'])) {
        $schedules['2days'] = array(
            'display' => __('Every 2 Day', 'ninja-photo-feed'),
            'interval' => 172800,
        );
    }

    if (!isset($schedules['3days'])) {
        $schedules['3days'] = array(
            'display' => __('Every 3 Day', 'ninja-photo-feed'),
            'interval' => 259200,
        );
    }

    if (!isset($schedules['1week'])) {
        $schedules['1week'] = array(
            'display' => __('Every 1 Week', 'ninja-photo-feed'),
            'interval' => 604800,
        );
    }

    if (!isset($schedules['2weeks'])) {
        $schedules['2weeks'] = array(
            'display' => __('Every 2 Week', 'ninja-photo-feed'),
            'interval' => 1209600,
        );
    }

    if (!isset($schedules['1month'])) {
        $schedules['1month'] = array(
            'display' => __('1 Month', 'ninja-photo-feed'),
            'interval' => 60 * 60 * 24 * 30,
        );
    }

    return $schedules;
});

add_filter('admin_footer_text', function ($footer_text) {
    $current_screen = get_current_screen();
    $is_npfeed_screen = ($current_screen && false !== strpos($current_screen->id, 'ninjaphotofeed'));
    if ($is_npfeed_screen) {
        $footer_text = sprintf(
            // translators: Please retain the placeholders (%s, %d, etc.) and ensure they are correctly used in context.
            __('Happy with Ninja Photo Feed? A quick %1$s review on %2$s would mean the world to us!', 'ninja-photo-feed'),
            '<a href="https://wordpress.org/support/plugin/ninja-photo-feed/reviews/#new-post" target="_blank" rel="nofollow">★★★★★</a>',
            '<a target="_blank" rel="nofollow" href="https://wordpress.org/support/plugin/ninja-photo-feed/reviews/#new-post">WordPress.org</a>'
        );
    }
    return $footer_text;
}, 11, 1);

/*
 * Exclude For WP Rocket Settings
 */
if (defined('WP_ROCKET_VERSION')) {
    add_filter('rocket_excluded_inline_js_content', function ($lines) {
        $lines[] = 'npfeed_ajax_params';
        $lines[] = 'NPFEED_';
        $lines[] = 'npfeed_';
        return $lines;
    });

    add_filter('rocket_exclude_defer_js', function ($defers) {
        $defers[] = str_replace(ABSPATH, '/', WP_PLUGIN_DIR) . '/ninja-photo-feed/assets/js/(.*).js';
        return $defers;
    });
}

add_filter('ninjaphotofeed/display_frontend_error_message', function ($platform, $error_message = '', $account_ids = [], $hashtags = '') {

    $errors = (new \NinjaPhotoFeed\App\Services\Platforms\PlatformErrorManager($platform))->getFrontEndErrors();

    if (!current_user_can('manage_options')) {
        return false;
    }

    if (!empty($errors) && is_array($errors)) {
        $inner_html = '';

        foreach ($errors as $index => $error) {
            if (!isset($error['error_message'])) {
                return false;
            }
            $allowed_tags = [
                'a' => ['href' => [], 'target' => []],
            ];

            $error_account_id = (new \NinjaPhotoFeed\App\Services\Platforms\PlatformErrorManager($platform))->connectedAccountHasError($account_ids, $index);
            if ((int)$error_account_id === (int)$index || $index === 'error_message' || (isset($error['hashtag']) && strpos($hashtags, '#' . $error['hashtag']) !== false) || (strpos($error['admin_only'], 'http_request_failed') !== false)) {
                $inner_html .= '<span>' . esc_html($error['error_message']) . '</span><br/>';
                $inner_html .= '<strong>' . wp_kses($error['admin_only'], $allowed_tags) . '</strong><br/><br/>';
            }
        }

        if (!empty($inner_html)) {
            $html = '<div class="npfeed_frontend_errors">';
            $html .= '<span>' . esc_html__('This error message is only visible to WordPress admins', 'ninja-photo-feed') . '</span><br/>';
            $html .= $inner_html;
            $html .= '</div>';
            return $html;
        }

        return false;
    }

    if (!empty($error_message)) {
        return '<div class="npfeed_frontend_errors">
            <span>' . __('This error message is only visible to WordPress admins', 'ninja-photo-feed') . '</span><br/>
            <strong>' . $error_message . '</strong>
       </div>';
    }

}, 10, 3);

// Ninja Photo Feed only ships Instagram; this closes off the YouTube/Twitter/Facebook/
// reviews code paths that the shared ShortcodeHandler/ImageOptimizationHandler would
// otherwise dispatch to, which this build does not include.
add_filter('ninjaphotofeed/shortcode_allowed_platforms', function () {
    return ['instagram'];
});

// TikTok is not a platform Ninja Photo Feed supports; these two frontend_vars keys are
// otherwise computed unconditionally on every page.
add_filter('ninjaphotofeed/frontend_vars', function ($vars) {
    unset($vars['is_custom_feed_for_tiktok_activated'], $vars['view_on_tiktok']);
    return $vars;
});

add_filter('plugin_row_meta', function ($meta, $plugin_file) {
    if ('ninja-photo-feed/ninja-photo-feed.php' === $plugin_file) {
        $row_meta = array(
            'support' => '<a rel="noopener" href="https://wordpress.org/support/plugin/ninja-photo-feed/" style="color: #197efb;font-weight: 600;" aria-label="' . esc_attr(esc_html__('Get Support', 'ninja-photo-feed')) . '" target="_blank">' . esc_html__('Support', 'ninja-photo-feed') . '</a>',
        );
        return array_merge($meta, $row_meta);
    }
    return (array)$meta;
}, 10, 2);
