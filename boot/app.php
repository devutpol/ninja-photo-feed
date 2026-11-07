<?php

defined('ABSPATH') or die;

use NinjaPhotoFeed\Framework\Foundation\Application;
use NinjaPhotoFeed\App\Hooks\Handlers\ActivationHandler;
use NinjaPhotoFeed\App\Hooks\Handlers\DeactivationHandler;

return function ($file) {

    $app = new Application($file);

    register_activation_hook($file, function ($network_wide) use ($app) {
        (new ActivationHandler)->handle($network_wide);
    });

    register_deactivation_hook($file, function () {
        (new DeactivationHandler)->handle();
    });

    add_action('plugins_loaded', function () use ($app) {
        // check the server here
        if (substr(phpversion(), 0, 3) == '7.0') {
            add_action('admin_notices', function () {
                $class = 'notice notice-error fc_message';
                $message = 'Looks like you are using PHP 7.0 which is not supported by Ninja Photo Feed. Please upgrade your PHP Version greater than to 7.2';
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
            });
        } else {
            do_action('ninja_photo_feed_loaded', $app);
            do_action('ninja_photo_feed_loaded_v2', $app);
        }
    });
};
