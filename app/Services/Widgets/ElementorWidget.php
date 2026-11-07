<?php

namespace NinjaPhotoFeed\App\Services\Widgets;
use Elementor\Plugin as Elementor;

/**
 * Instagram-only version of core's ElementorWidget. Registers/enqueues only the Instagram
 * widget and style; the Reviews/YouTube/Twitter/Facebook registrations this build never ships
 * are dropped (they were already file_exists()-guarded no-ops here, never missing-class fatals).
 */
class ElementorWidget
{
    public function __construct()
    {
        add_action( 'elementor/frontend/after_register_styles', [$this, 'registerAssets'], 10);
        add_action( 'elementor/frontend/after_enqueue_styles', [$this, 'enqueueAssets'], 10);

        add_action( 'elementor/widgets/register', [$this, 'init_widgets'] );
        add_action( 'elementor/init', [ $this, 'elementor_init' ] );
    }

    public function elementor_init() {
        // Add element category in panel
        Elementor::instance()->elements_manager->add_category(
            'ninja-photo-feed',
            [
                'title' => __( 'Ninja Photo Feed', 'ninja-photo-feed' ),
                'icon' => 'font',
            ],
            1
        );
    }

    public function enqueueAssets()
    {
        global $post;
        $post_id = isset($post) && isset($post->ID) ? $post->ID : null;

        $npfeed_elementor_ids = get_post_meta($post_id, '_npfeed_elementor_ids', true);

        if (!empty($npfeed_elementor_ids) && in_array('ig', $npfeed_elementor_ids)) {
            wp_enqueue_style('npfeed_ig');
        }
    }

    public function init_widgets()
    {
        $widgets_manager = Elementor::instance()->widgets_manager;

        if ( file_exists( NINJAPHOTOFEED_DIR.'app/Services/Widgets/InstagramWidget.php' ) ) {
            require_once NINJAPHOTOFEED_DIR.'app/Services/Widgets/InstagramWidget.php';
            $widgets_manager->register( new InstagramWidget() );
        }
    }

    public function registerAssets()
    {
        wp_register_style(
            'npfeed_ig',
            NINJAPHOTOFEED_URL . 'assets/css/ninja-photo-feed.css',
            array(),
            NINJAPHOTOFEED_VERSION
        );
    }
}
