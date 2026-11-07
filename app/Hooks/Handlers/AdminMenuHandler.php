<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

use NinjaPhotoFeed\App\App;
use NinjaPhotoFeed\App\Services\Helper;
use NinjaPhotoFeed\App\Services\PermissionManager;
use NinjaPhotoFeed\App\Services\TranslationString;
use NinjaPhotoFeed\Framework\Support\Arr;

/**
 * Register Menu and Admin Pages for Ninja Photo Feed
 * @since 1.0.0
 */
class AdminMenuHandler
{
    /**
     * Add Menu and sub menu for the admin page
     * @return string
     * @since 1.0.0
     */
    public function addMenus()
    {
        $permission = PermissionManager::currentUserPermissions();

        if (empty($permission)) {
            return;
        }

        $hasAllPermission = in_array('administrator', $permission) || in_array('npfeed_full_access', $permission);
        $dashboardPermission = $permission[0];

        if (in_array('administrator', $permission)) {
            $dashboardPermission = 'manage_options';
        }

        if (in_array('npfeed_full_access', $permission)) {
            $permission = PermissionManager::pluginPermissions();
        }

        $title = __('Ninja Photo Feed', 'ninja-photo-feed');
        // Shorter than $title so it doesn't wrap onto a second line in the narrow admin sidebar.
        $menuTitle = __('Ninja Photo Feed', 'ninja-photo-feed');

        global $submenu;
        add_menu_page(
            $title,
            $menuTitle,
            $dashboardPermission,
            'ninjaphotofeed.php',
            array($this, 'render'),
            $this->getIcon(),
            25
        );

        if (in_array('npfeed_manage_platforms', $permission)) {
            $submenu['ninjaphotofeed.php']['platforms'] = apply_filters('ninjaphotofeed/menu_item_platforms', [
                __('Instagram Feed', 'ninja-photo-feed'),
                $hasAllPermission ? $dashboardPermission : 'npfeed_manage_platforms',
                'admin.php?page=ninjaphotofeed.php#/',
            ]);
        }

        if (in_array('npfeed_manage_templates', $permission)) {
            $submenu['ninjaphotofeed.php']['templates'] = array(
                __('Templates', 'ninja-photo-feed'),
                $hasAllPermission ? $dashboardPermission : 'npfeed_manage_templates',
                'admin.php?page=ninjaphotofeed.php#/templates',
            );
        }

        $submenu['ninjaphotofeed.php']['settings'] = array(
            __('Settings', 'ninja-photo-feed'),
            $dashboardPermission,
            'admin.php?page=ninjaphotofeed.php#/settings/instagram-settings',
        );
    }

    /**
     * Render the admin app container
     * @return string
     * @since 1.0.0
     */
    public function render()
    {
        echo "<div id='ninjaphotofeedapp'></div>";
        do_action('ninjaphotofeed/loading_app');
    }

    /**
     * SVG icon for menu
     * @return string
     * @since 1.0.0
     */
    public function getIcon()
    {
        // Ninja Photo Feed's own mark, a grid of photo tiles; WordPress recolours it to the admin scheme.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#fff"><path fill-rule="evenodd" d="M3.6 2h4.3a1.6 1.6 0 0 1 1.6 1.6v4.3a1.6 1.6 0 0 1-1.6 1.6H3.6A1.6 1.6 0 0 1 2 7.9V3.6A1.6 1.6 0 0 1 3.6 2zm-.2 6.2l1.9-2.3 1.2 1.4.8-.9 1.4 1.8zm3.15-3.6a.75.75 0 1 0 1.5 0 .75.75 0 1 0-1.5 0z"/><rect x="10.5" y="2" width="7.5" height="7.5" rx="1.6" fill-opacity=".6"/><rect x="2" y="10.5" width="7.5" height="7.5" rx="1.6" fill-opacity=".6"/><rect x="10.5" y="10.5" width="7.5" height="7.5" rx="1.6"/></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Enqueue all js file which are needed for admin side
     * @since 1.0.0
     */
    public function enqueueAssets()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking for admin page context, not processing form data
        if (isset($_GET['page']) && $_GET['page'] == 'ninjaphotofeed.php') {
            $app = App::getInstance();

            add_action('wp_print_scripts', function () {
                $isSkip = apply_filters('npfeed_skip_no_conflict', false);

                if ($isSkip) {
                    return;
                }

                global $wp_scripts;
                if (!$wp_scripts) {
                    return;
                }

                $themeUrl = content_url('themes');
                $pluginUrl = plugins_url();
                foreach ($wp_scripts->queue as $script) {
                    if (empty($wp_scripts->registered[$script])) {
                        continue;
                    }

                    $src = $wp_scripts->registered[$script]->src;
                    $isMatched = strpos($src, $pluginUrl) !== false && strpos($src, NINJAPHOTOFEED_URL) === false;
                    if (!$isMatched) {
                        continue;
                    }
                    wp_dequeue_script($wp_scripts->registered[$script]->handle);
                }

            }, 1);

            if (function_exists('wp_enqueue_editor')) {
                wp_enqueue_editor();
                wp_enqueue_script('thickbox');
            }

            if (function_exists('wp_enqueue_media')) {
                add_filter('user_can_richedit', '__return_true');
                wp_enqueue_media();
            }

            wp_enqueue_script('ninjaphotofeed_boot', NINJAPHOTOFEED_URL . 'assets/js/ninja-photo-feed-boot.js',
                array('jquery'), NINJAPHOTOFEED_VERSION, true);

            do_action('ninjaphotofeed/booting_admin_app');

            wp_enqueue_script('ninjaphotofeed_admin_app', NINJAPHOTOFEED_URL . 'assets/js/ninja-photo-feed-admin.js',
                array('ninjaphotofeed_boot'), NINJAPHOTOFEED_VERSION, true);

            wp_enqueue_script('jquery-masonry');
            wp_enqueue_script('imagesloaded');

            wp_enqueue_style('ninjaphotofeed_admin_app', NINJAPHOTOFEED_URL . 'assets/css/ninja-photo-feed-admin.css',
                array(), NINJAPHOTOFEED_VERSION);

            $upload = wp_upload_dir();
            $upload_url = trailingslashit($upload['baseurl']) . trailingslashit(NINJAPHOTOFEED_UPLOAD_DIR_NAME);

            $socialPhotoAdminVars = apply_filters('ninjaphotofeed/admin_app_vars', array(
                'i18n'                    => TranslationString::getStrings(),
                'npfeed_admin_nonce'         => wp_create_nonce('npfeed_admin_nonce'),
                'assets_url'              => NINJAPHOTOFEED_URL . 'assets',
                'has_pro'                 => (defined('NINJAPHOTOFEED_PRO') && NINJAPHOTOFEED_PRO),
                // Without Pro, Pro features are left out of the admin rather than shown locked.
                'hide_pro_features'       => !(defined('NINJAPHOTOFEED_PRO') && NINJAPHOTOFEED_PRO),
                'instagram_pro_templates' => InstagramTemplateHandler::getProTemplates(),
                'auth_service_url'        => apply_filters('ninjaphotofeed/auth_service_url', NINJAPHOTOFEED_AUTH_BASE_URL),
                'ajaxurl'                 => admin_url('admin-ajax.php'),
                'admin_page_url'          => admin_url('admin.php?page=ninjaphotofeed.php'),
                'admin_url'               => admin_url('admin.php'),
                'slug'                    => $slug = $app->config->get('app.slug'),
                'nonce'                   => wp_create_nonce($slug),
                'rest'                    => $this->getRestInfo($app),
                'upload_url'              => $upload_url,
                'image_format'            => Helper::getOptimizeImageFormat(),
                'auth'                    => [
                    'permissions' => PermissionManager::currentUserPermissions()
                ],
            ));

            $socialPhotoAdminVars['platforms_cards'] = $this->getPlatformsInfo();

            wp_localize_script('ninjaphotofeed_boot', 'NinjaPhotoFeedAdmin', $socialPhotoAdminVars);

            add_filter('update_footer', function ($text) {
                return 'Version ' . NINJAPHOTOFEED_VERSION;
            }, 11, 1);
        }
    }

    protected function cleanRest($url)
    {
        $len = strlen($url);
        while ($len > 1 && $url[$len - 1] == '/') {
            $url = substr($url, 0, -1);
            $len = strlen($url);
        }
        return $url;
    }

    protected function getRestInfo($app)
    {
        $ns = $app->config->get('app.rest_namespace');
        $v = $app->config->get('app.rest_version');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => $this->cleanRest(rest_url($ns . '/' . $v)),
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $v,
        ];
    }

    private function getPlatformsInfo()
    {
        $assetBase = NINJAPHOTOFEED_URL . 'assets/images/icon/';

        return apply_filters('ninjaphotofeed/platforms_info', [
            [
                'id'                 => 1,
                'platform'           => 'instagram',
                'platform_title'     => __('Instagram', 'ninja-photo-feed'),
                'image'              => $assetBase . 'icon-instagram-small.png',
                'apiKey'             => '',
                'sourceId'           => '',
                'message'            => '',
                'feedsinfo'          => [],
                'sourceText'         => 'Account',
                'apiUrl'             => '',
                'sourceUrl'          => '',
                'exampleURL'         => '',
                'count'              => 0,
                'privacy'            => 'https://wpsocialninja.com/privacy-policy/',
                'termsAndConditions' => 'https://wpsocialninja.com/terms-conditions/',
            ],
        ]);
    }
}
