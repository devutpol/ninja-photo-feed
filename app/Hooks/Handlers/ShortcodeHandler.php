<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

use Exception;
use NinjaPhotoFeed\App\Services\Platforms\Reviews\Helper;
use NinjaPhotoFeed\App\Services\Helper as GlobalHelper;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Config;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\InstagramFeed;
use NinjaPhotoFeed\Framework\Foundation\App;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\GlobalSettings;

/**
 * Instagram-only version of core's ShortcodeHandler. The other platforms' render
 * methods and their reviews/QR/social-wall helpers are dropped: this build's
 * shortcodeAllowedPlatforms() restricts rendering to 'instagram', and none of their
 * platform classes ship here.
 */
class ShortcodeHandler
{
    public $platform = '';
    public $imageSettings = [];
    public $productId = null;
    private static $localizeScriptLoaded = false;

    public function addShortcode()
    {
        add_shortcode('ninja_photo_feed', array($this, 'makeShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 999);
        add_action('npfeed_add_layout_script', array($this, 'enqueueScripts'));
        add_filter('script_loader_tag', array($this, 'addFrontendVarsBeforeScript'), 10, 3);
    }

    public function makeShortcode($args = [], $content = null, $tag = '')
    {
        // Skip processing during Elementor save/editor to prevent memory exhaustion
        if (defined('ELEMENTOR_VERSION')) {
            // skip during REST API requests when Elementor is active (block editor compatibility)
            if (defined('REST_REQUEST') && REST_REQUEST) {
                return '';
            }
        }

        $args = shortcode_atts(array(
            'id' => '',
            'platform' => '',
            'product_id' => '',
        ), $args);

        if (!$args['id']) {
            return '';
        }
        $platform = sanitize_text_field(Arr::get($args, 'platform', ''));
        $product_id = absint(Arr::get($args, 'product_id', 0));

        if (empty($platform) || empty($args['id'])) {
            return __('Please, set a template platform name or template id on your shortcode', 'ninja-photo-feed');
        }

        $templateId = absint($args['id']);

        if (!in_array($platform, GlobalHelper::shortcodeAllowedPlatforms())) {
            return __('Provided platform name is not valid.', 'ninja-photo-feed');
        }

        // Same visibility policy as the AJAX renderers: never render a draft, private,
        // password-protected, or wrong-post-type template on a public page. Users who
        // can edit the template still get admin previews via the edit_post fallback.
        if (!$this->isPublicRenderableTemplate($templateId)) {
            return '';
        }

        $this->platform = $platform;
        $this->productId = $product_id;

        if (!did_action('wp_enqueue_scripts')) {
            $this->registerStyles();
        }

        $this->enqueueStyles([$this->platform]);

        // $platform is always 'instagram' here — shortcodeAllowedPlatforms() already rejected anything else.
        $this->imageSettings = Helper::getImageSettings($platform);

        return $this->renderInstagramTemplate($templateId, $platform);
    }

    /**
     * Check whether a template id may be rendered on a public (unauthenticated) request.
     *
     * Template configs live in post meta, so the public AJAX renderers would otherwise
     * accept any post id — including unpublished/draft/private templates and posts
     * belonging to other plugin post types that share the `_npfeed_template_config`
     * meta key (notifications, chat widgets). The `npfeed-ajax-nonce` used by those
     * endpoints is printed for logged-out visitors, so it authenticates nothing here.
     *
     * Users who can edit the template keep access, so admin-side previews still work.
     *
     * @param int $npfeed_template_id
     * @param array $npfeed_extra_post_types Extra post types this caller may serve; every other check still applies.
     *
     * @return bool
     * @since 4.3.1
     */
    public function isPublicRenderableTemplate($npfeed_template_id, $npfeed_extra_post_types = [])
    {
        $npfeed_template_id = absint($npfeed_template_id);

        if (empty($npfeed_template_id)) {
            return false;
        }

        $npfeed_template_post = get_post($npfeed_template_id);

        if (empty($npfeed_template_post)) {
            return false;
        }

        $npfeed_allowed_post_types = apply_filters(
            'ninjaphotofeed/public_renderable_template_post_types',
            array_merge(['npfeed_template'], (array) $npfeed_extra_post_types)
        );

        if (!in_array($npfeed_template_post->post_type, $npfeed_allowed_post_types, true)) {
            return false;
        }

        // A published but password-protected template must still satisfy the post
        // password challenge; anonymous AJAX callers never do, so fall through to the
        // edit_post capability check (admin previews) rather than exposing its feed.
        if ($npfeed_template_post->post_status === 'publish' && !post_password_required($npfeed_template_post)) {
            return true;
        }

        return current_user_can('edit_post', $npfeed_template_id);
    }

    public function templateMeta($templateId, $platform)
    {
        $this->platform = $platform;
        $encodedMeta = get_post_meta($templateId, '_npfeed_template_config', true);
        $template_meta = json_decode($encodedMeta, true);
        if (empty($template_meta)) {
            return ['error_message' => __('No template is available for this shortcode!!', 'ninja-photo-feed')];
        }

        $configs = Arr::get($template_meta, 'feed_settings', []);
        if ($platform === 'instagram') {
            $template_meta = Config::formatInstagramConfig($configs, []);
        } else {
            $template_meta = apply_filters('ninjaphotofeed/format_'. $platform .'_config', $configs, []);
        }

        if (!Arr::get($template_meta, 'feed_settings.platform')) {
            return [
                'error_message' => __('Please set a template platform name on your shortcode', 'ninja-photo-feed')
            ];
        }

        return $template_meta;
    }

    public function renderInstagramTemplate($templateId, $platform)
    {
        $html = '';
        // Clear LiteSpeed cache if plugin is active
        if (defined('LSCWP_V')) {
            do_action('litespeed_tag_add', 'npfeed_purge_instagram'); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        }

        $app = App::getInstance();
        $template_meta = $this->templateMeta($templateId, $platform);

        $account_ids = Arr::get($template_meta, 'feed_settings.source_settings.account_ids');

        do_action('ninjaphotofeed/before_display_instagram_feed', $account_ids);

        if (!empty($template_meta['error_message'])) {
            return apply_filters('ninjaphotofeed/display_frontend_error_message', $platform, $template_meta['error_message']);
        }

        $feed = (new InstagramFeed())->getTemplateMeta($template_meta, $templateId);
        $settings = $this->formatFeedSettings($feed);

        //template mapping
        $templateMapping = [
            'template1' => 'public.feeds-templates.instagram.template1',
            'template2' => 'public.feeds-templates.instagram.template2',
            'template5' => 'public.feeds-templates.instagram.template2',
            'template6' => 'public.feeds-templates.instagram.template1',
        ];
        $template = Arr::get($settings['feed_settings'], 'template', '');
        $isProTemplate = InstagramTemplateHandler::canRenderProTemplate($template);

        // Mirrors Facebook: an unrenderable template falls back instead of blanking the feed.
        if (!isset($templateMapping[$template]) && !$isProTemplate) {
            $template = 'template1';
        }
        $file = isset($templateMapping[$template]) ? $templateMapping[$template] : '';

        $layout = Arr::get($settings, 'feed_settings.layout_type');
        do_action('npfeed_loading_layout_' . $layout, $templateId, $settings);

        //pagination settings
        $pagination_settings = $this->formatPaginationSettings($feed);
        // Load More pages come from Pro's handler; without it the whole feed shows on one page.
        if ($pagination_settings['pagination_type'] === 'load_more' && !has_filter('npfeed_feed_items_by_page_instagram')) {
            $pagination_settings['pagination_type'] = 'none';
            $pagination_settings['maxId'] = is_array($settings['feeds']) ? count($settings['feeds']) : 0;
        }
        $translations = GlobalSettings::getTranslations();
        $feeds = Arr::get($settings, 'dynamic.items', []);

        $hasLatestPost = Arr::get($settings, 'dynamic.has_latest_post', false);
        //enable when gdpr is on
        $resizedImages = Arr::get($settings, 'dynamic.resize_data', []);
        if(Arr::get($this->imageSettings, 'optimized_images', 'false') === 'true' && (count($resizedImages) < count($feeds) || $hasLatestPost)) {
            wp_enqueue_script('npfeed-image-resizer');
        }

        if (Arr::get($this->imageSettings, 'optimized_images', 'false') === 'true' || Arr::get($settings['feed_settings'], 'pagination_settings.pagination_type') != 'none') {
            $this->enqueueScripts();
        }

        do_action('ninjaphotofeed/load_template_assets', $templateId);

        $settings = apply_filters('ninjaphotofeed/get_shoppable_feeds', $settings);

        if(defined('NINJAPHOTOFEED_PRO') && class_exists('\NinjaPhotoFeedPro\App\Services\Platforms\Feeds\Shoppable')){
            $settings = (new \NinjaPhotoFeedPro\App\Services\Platforms\Feeds\Shoppable())->makeShoppableFeeds($settings, 'instagram');
        }

        $error_message = Arr::get($settings, 'dynamic.error_message');
        $hashtags = Arr::get($settings, 'feed_settings.source_settings.hash_tags');

        if (Arr::get($error_message, 'error_message')) {
            $html .= apply_filters('ninjaphotofeed/display_frontend_error_message', $platform, $error_message['error_message'], $account_ids, $hashtags);
        } else {
            $html .= apply_filters('ninjaphotofeed/display_frontend_error_message', $platform, $error_message, $account_ids, $hashtags);
        }

        $html .= $app->view->make('public.feeds-templates.instagram.header', array(
            'templateId' => $templateId,
            'template' => $template,
            'header' => $settings['header'],
            'feed_settings' => $settings['feed_settings'],
            'layout_type' => $settings['layout_type'],
            'column_gaps' => $settings['column_gaps'],
            'translations' => $translations,
        ));

        if(!empty($feeds)) {
            $template_body_data = array(
                'templateId'    => $templateId,
                'feeds'         => $feeds,
                'template_meta' => $settings['feed_settings'],
                'sinceId'       => $pagination_settings['sinceId'],
                'maxId'         => $pagination_settings['maxId'],
                'image_settings' => $this->imageSettings
            );

            if (in_array($template, array('template5', 'template6'), true)) {
                $template_body_data['template_meta']['_header_data']['avatar_url'] = (new InstagramTemplateHandler())->getUserAvatar($settings['header'], Arr::get($settings, 'feed_settings.header_settings', []));
            }

            if ($isProTemplate) {
                $template_body_data['header'] = $settings['header'];
                $npfeed_pro_html = apply_filters('ninjaphotofeed/add_instagram_feed_template', $template_body_data);

                // No renderer ran, so the filter handed back the array it was given.
                $html .= is_string($npfeed_pro_html)
                    ? $npfeed_pro_html
                    : $app->view->make($templateMapping['template1'], $template_body_data);
            } else {
                $html .= $app->view->make($file, $template_body_data);
            }
        }

        $html .= $app->view->make('public.feeds-templates.instagram.footer',
            array(
                'templateId' => $templateId,
                'feeds' => $feeds,
                'feed_settings' => $settings['feed_settings'],
                'layout_type' => $settings['layout_type'],
                'column_gaps' => $settings['column_gaps'],
                'paginate' => $pagination_settings['paginate'],
                'pagination_type' => $pagination_settings['pagination_type'],
                'total' => $pagination_settings['total'],
        ));

        return $html;
    }

    public function formatFeedSettings($feed = [])
    {
        $feed_settings = Arr::get($feed, 'feed_settings', []);
        $filterSettings = Arr::get($feed_settings, 'filters', []);
        $dynamic = Arr::get($feed, 'dynamic', $feed);
        $feeds = Arr::get($dynamic, 'items', []);
        $header = Arr::get($dynamic, 'header', []);
        $layout_type = Arr::get($feed_settings, 'layout_type', 'grid');
        $column_gaps = Arr::get($feed_settings, 'column_gaps', 'default');

        return [
            'feeds'           => $feeds,
            'header'          => $header,
            'feed_settings'   => $feed_settings,
            'filter_settings' => $filterSettings,
            'layout_type'     => $layout_type,
            'column_gaps'     => $layout_type !== 'carousel' ? $column_gaps : null,
            'dynamic'         => $dynamic,
        ];
    }

    public function formatPaginationSettings($feed = [])
    {
        $settings = $this->formatFeedSettings($feed);
        $sinceId = 0;

        // Get responsive paginate_number if available
        $paginateNumber = Arr::get($settings, 'feed_settings.pagination_settings.paginate_number', []);
        $fallbackPaginate = intval(Arr::get($settings, 'feed_settings.pagination_settings.paginate', 6));

        // Use responsive paginate_number if available, otherwise fall back to paginate
        if (!empty($paginateNumber)) {
            $paginate = wp_is_mobile()
                ? (int) Arr::get($paginateNumber, 'mobile', $fallbackPaginate)
                : (int) Arr::get($paginateNumber, 'desktop', $fallbackPaginate);
        } else {
            $paginate = $fallbackPaginate;
        }

        $maxId = ($sinceId + $paginate) - 1;
        $totalFeed = is_array($settings['feeds']) ? count($settings['feeds']) : 0;

        $numOfFeeds = Arr::get($settings, 'filter_settings.total_posts_number');
        $totalFilterFeed = wp_is_mobile() ? Arr::get($numOfFeeds, 'mobile') : Arr::get($numOfFeeds, 'desktop');
        $total = (int)($totalFilterFeed && $totalFeed < $totalFilterFeed) ? $totalFeed : $totalFilterFeed;

        $pagination_type = Arr::get($settings['feed_settings'], 'pagination_settings.pagination_type', 'none');

        if ($settings['layout_type'] === 'carousel' || $pagination_type === 'none') {
            $maxId = $totalFeed;
        }

        return [
            'sinceId'         => $sinceId,
            'maxId'           => $maxId,
            'paginate'        => $paginate,
            'total'           => $total,
            'pagination_type' => $pagination_type,
        ];
    }

    /**
     *  Enqueue All Front-End Assets
     */
    public function registerScripts()
    {
        wp_register_script('npfeed-image-resizer', NINJAPHOTOFEED_URL . 'assets/js/image_resizer.js',
            array('jquery'), NINJAPHOTOFEED_VERSION, true);

        wp_register_script('npfeed-public', NINJAPHOTOFEED_URL . 'assets/js/ninja-photo-feed.js',
            array('jquery'), NINJAPHOTOFEED_VERSION, true);

        $this->registerStyles();

        global $post;
        $post_id = $post->ID ?? null;
        do_action('ninjaphotofeed/enqueue_product_context_styles', $post_id);

        // load assets in single page/products
        if ((is_a($post, 'WP_Post') && $shortcodeIds = get_post_meta($post_id, '_npfeed_ids', true))) {
            $this->enqueueStyles($shortcodeIds);
        }
    }

    public function registerStyles()
    {
        wp_register_style(
            'npfeed_ig',
            NINJAPHOTOFEED_URL . 'assets/css/ninja-photo-feed.css',
            array(),
            NINJAPHOTOFEED_VERSION
        );
    }

    public function enqueueStyles($platformNames = [])
    {
        if (!$platformNames) {
            return false;
        }

        foreach ($platformNames as $platform) {
            wp_enqueue_style('npfeed_ig');
        }
    }

    /**
     * Build the localization params array for frontend scripts.
     *
     * @return array
     */
    private function buildLocalizeParams()
    {
        $upload     = wp_upload_dir();
        $upload_url = trailingslashit($upload['baseurl']) . NINJAPHOTOFEED_UPLOAD_DIR_NAME;
        $translations = GlobalSettings::getTranslations();
        $platform = $this->platform ?: '';
        $image_settings = !empty($this->imageSettings) ? $this->imageSettings : Helper::getImageSettings($platform ?: 'instagram');
        $image_settings = is_array($image_settings) ? $image_settings : [];
        $image_settings = wp_parse_args($image_settings, [
            'optimized_images' => 'false',
            'has_gdpr'         => 'false',
            'image_format'     => GlobalHelper::getOptimizeImageFormat(),
        ]);
        $image_settings['image_format'] = in_array($image_settings['image_format'], ['jpg', 'webp'], true) ? $image_settings['image_format'] : 'jpg';

        return apply_filters('ninjaphotofeed/frontend_vars', array(
            'ajax_url'   => admin_url('admin-ajax.php'),
            'npfeed_nonce' => wp_create_nonce('npfeed-ajax-nonce'),
            'has_pro'    => defined('NINJAPHOTOFEED_PRO'),
            'read_more'  => Arr::get($translations, 'read_more') ?: __('Read more', 'ninja-photo-feed'),
            'read_less'  => Arr::get($translations, 'read_less') ?: __('Read less', 'ninja-photo-feed'),
            'likes'      => Arr::get($translations, 'likes') ?: __( 'likes', 'ninja-photo-feed' ),
            'plugin_url' => NINJAPHOTOFEED_URL,
            'image_settings'   => $image_settings,
            'upload_url' => $upload_url,
            'user_role' => current_user_can('administrator'),
            'a11y'       => [
                    'prevSlideMessage' => __('Previous slide', 'ninja-photo-feed'),
                    'nextSlideMessage' => __('Next slide', 'ninja-photo-feed'),
                    'firstSlideMessage' => __('This is the first slide', 'ninja-photo-feed'),
                    'lastSlideMessage' => __('This is the last slide', 'ninja-photo-feed'),
                    // translators: Please retain the placeholders (%s, %d, etc.) and ensure they are correctly used in context.
                    'paginationBulletMessage' => sprintf(__('Go to slide %s', 'ninja-photo-feed'), '{{index}}'),
            ]
        ));
    }

    /**
     * Build the inline JS string that defines window.npfeed_ajax_params.
     *
     * @return string
     */
    private function buildLocalizeJs()
    {
        $params = $this->buildLocalizeParams();
        return 'window.npfeed_ajax_params = ' . wp_json_encode($params) . ';';
    }

    /**
     * @deprecated Use enqueueScripts() instead.
     * Kept public for backward compatibility with Oxygen widget integrations.
     */
    public function loadLocalizeScripts()
    {
        static $jsLoaded;

        if ($jsLoaded) {
            return;
        }

        $this->enqueueScripts();
        $jsLoaded = true;
    }

    public function enqueueScripts()
    {
        static $jsLoaded;

        if ($jsLoaded) {
            return;
        }

        wp_enqueue_script('npfeed-public');
        $jsLoaded = true;
    }

    public function addFrontendVarsBeforeScript($tag, $handle, $src)
    {
        if ($handle !== 'npfeed-public' || self::$localizeScriptLoaded) {
            return $tag;
        }

        self::$localizeScriptLoaded = true;

        return wp_get_inline_script_tag(
            $this->buildLocalizeJs(),
            array('id' => 'wp-social-review-js-before')
        ) . $tag;
    }

    public function handleLoadMoreAjax()
    {
        if (!check_ajax_referer('npfeed-ajax-nonce', 'security', false)) {
            wp_send_json_error([
                'message' => __('Invalid nonce.', 'ninja-photo-feed')
            ], 403);
        }

        $templateId = absint(Arr::get($_REQUEST, 'template_id'));

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading AJAX parameters for pagination, not processing sensitive form data
        $platform = sanitize_text_field(Arr::get($_REQUEST, 'platform'));

        if (!in_array($platform, GlobalHelper::shortcodeAllowedPlatforms(), true)) {
            wp_send_json_error(['message' => __('Invalid platform.', 'ninja-photo-feed')], 403);
        }

        if (!$this->isPublicRenderableTemplate($templateId)) {
            wp_send_json_error([
                'message' => __('Invalid template.', 'ninja-photo-feed')
            ], 403);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading AJAX parameters for pagination, not processing sensitive form data
        $page = absint(Arr::get($_REQUEST, 'page', 2));

        $content = apply_filters('npfeed_feed_items_by_page_' . $platform, '', $templateId, $page);

        wp_send_json(['content' => $content]);
        die();
    }
}
