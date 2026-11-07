<?php
/*
Plugin Name:  Ninja Photo Feed
Plugin URI:   https://github.com/devutpol/ninja-photo-feed
Description:  Display your Instagram feed beautifully on your WordPress website.
Version:      1.0.0
Requires at least: 6.2
Requires PHP: 7.4
Author:       Utpol Debnath
Author URI:   https://github.com/devutpol
License:      GPLv2 or later
Text Domain:  ninja-photo-feed
Domain Path:  /language
*/

defined('ABSPATH') or die;

define('NINJAPHOTOFEED_VERSION', '1.0.0');
define('NINJAPHOTOFEED_DB_VERSION', 100);
define('NINJAPHOTOFEED_MAIN_FILE', __FILE__);
define('NINJAPHOTOFEED_BASENAME', plugin_basename(__FILE__));
define('NINJAPHOTOFEED_URL', plugin_dir_url(__FILE__));
define('NINJAPHOTOFEED_DIR', plugin_dir_path(__FILE__));
define('NINJAPHOTOFEED_UPLOAD_DIR_NAME', 'ninja-photo-feed');

define('NINJAPHOTOFEED_AUTH_BASE_URL', 'https://connect.wpsocialninja.com');

if (!defined('NINJAPHOTOFEED_INSTAGRAM_MAX_RECORDS')) {
    define('NINJAPHOTOFEED_INSTAGRAM_MAX_RECORDS', 300);
}

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));

// Handle Network new Site Activation
add_action('wp_insert_site', function ($new_site) {
    if (is_plugin_active_for_network('ninja-photo-feed/ninja-photo-feed.php')) {
        switch_to_blog($new_site->blog_id);
        (new \NinjaPhotoFeed\App\Hooks\Handlers\ActivationHandler())->handle(false);
        restore_current_blog();
    }
});
