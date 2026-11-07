<?php
namespace NinjaPhotoFeed\App\Services\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use NinjaPhotoFeed\Framework\Support\Arr;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class InstagramWidget extends Widget_Base {

    public function get_name() {
        return 'ninja-photo-feed-instagram-widget';
    }

    public function get_title() {
        return __( 'Ninja Photo Feed', 'ninja-photo-feed' );
    }

    public function get_icon() {
        return 'eicon-instagram-gallery';
    }

    public function get_keywords() {
        return [
            'social photo feed',
            'npfeed',
            'photo feed',
            'instagram',
            'feed',
            'instagram feed'
        ];
    }

    public function get_categories() {
        return array('ninja-photo-feed');
    }

    public function get_style_depends() {
        return ['npfeed_ig'];
    }

    public function get_script_depends() {
        return [];
    }

    protected function register_controls()
    {
        $this->register_general_controls();
        $this->register_ig_header_style_controls();
        $this->register_ig_content_style_controls();
        $this->register_ig_statistics_style_controls();
        $this->register_pagination_style_controls();
        $this->register_ig_box_style_controls();
    }

    protected function register_general_controls(){
        $platforms = ['instagram'];

        $this->start_controls_section(
            'section_social_ninja_instagram_templates',
            [
                'label' => __('Ninja Photo Feed', 'ninja-photo-feed'),
            ]
        );

        $this->add_control(
            'instagram_template_list',
            [
                'label' => __('Select a Template', 'ninja-photo-feed'),
                'type' => Controls_Manager::SELECT,
                'label_block' => true,
                'options' => Helper::getTemplates($platforms),
                'default' => '0',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_ig_header_style_controls()
    {
        $this->start_controls_section(
            'section_instagram_header_style',
            [
                'label' => __('Header', 'ninja-photo-feed'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'instagram_header_username_title',
            [
                'label' => __('UserName', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'instagram_header_username_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-name a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'instagram_header_username_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-name a',
            ]
        );

        $this->add_responsive_control(
            'instagram_header_username_spacing',
            [
                'label' => esc_html__('Bottom Spacing', 'ninja-photo-feed'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'instagram_header_statistics_title',
            [
                'label' => __('Statistics', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'instagram_header_statistics_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-statistics .npfeed-ig-header-statistic-item' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'instagram_header_statistics_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-statistics .npfeed-ig-header-statistic-item',
            ]
        );

        $this->add_responsive_control(
            'instagram_header_statistics_spacing',
            [
                'label' => esc_html__('Spacing Between Item', 'ninja-photo-feed'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-info .npfeed-ig-header-statistics .npfeed-ig-header-statistic-item' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'instagram_header_fullname_title',
            [
                'label' => __('FullName', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'instagram_header_fullname_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-fullname' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'instagram_header_fullname_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-fullname',
            ]
        );

        $this->add_control(
            'instagram_header_description_title',
            [
                'label' => __('Description', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'instagram_header_description_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-description p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'instagram_header_description_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-description p',
            ]
        );

        $this->add_control(
            'instagram_header_follow_btn_title',
            [
                'label' => __('Follow Button', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'instagram_header_follow_btn_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-follow-btn a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'instagram_header_follow_btn_bg_color',
            [
                'label' => __('Button Background Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-follow-btn a' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'instagram_header_follow_btn_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-follow-btn a',
            ]
        );

        $this->add_control(
            'instagram_header_box_title',
            [
                'label' => __('Box', 'ninja-photo-feed'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'instagram_header_bg_color',
            [
                'label' => __('Background Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header .npfeed-ig-header-inner' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'instagram_header_padding',
            [
                'label' => esc_html__('Padding', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header .npfeed-ig-header-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'instagram_header_box_border',
                'selector' => '{{WRAPPER}} .npfeed-ig-header .npfeed-ig-header-inner',
            ]
        );

        $this->add_control(
            'instagram_header_border_radius',
            [
                'label' => esc_html__('Border Radius', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-header .npfeed-ig-header-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'instagram_header_box_shadow',
                'selector' => '{{WRAPPER}} .npfeed-ig-header .npfeed-ig-header-inner',
            ]
        );

        $this->end_controls_section();

    }

    protected function register_ig_content_style_controls()
    {
        $this->start_controls_section(
            'section_ig_content_style',
            [
                'label' => __('Content', 'ninja-photo-feed'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'ig_content_text_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'ig_content_text_hashtag_color',
            [
                'label' => __('Hashtag Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p a' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'ig_content_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p',
            ]
        );

        $this->add_responsive_control(
            'ig_content_margin',
            [
                'label' => __('Margin', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'placeholder' => [
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ig_content_padding',
            [
                'label' => esc_html__('Padding', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_ig_statistics_style_controls()
    {
        $this->start_controls_section(
            'section_ig_statistics_style',
            [
                'label' => __('Statistics', 'ninja-photo-feed'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'ig_statistics_text_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic span' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'ig_statistics_icon_color',
            [
                'label' => __('Icon Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic svg' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'ig_statistics_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic span',
            ]
        );

        $this->add_responsive_control(
            'ig_statistics_margin',
            [
                'label' => __('Margin', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'placeholder' => [
                    'top' => '',
                    'right' => '',
                    'bottom' => '',
                    'left' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ig_statistics_spacing',
            [
                'label' => esc_html__('Spacing Between Item', 'ninja-photo-feed'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_pagination_style_controls(){

        $this->start_controls_section(
            'section_ig_pagination_style',
            [
                'label' => __('Pagination', 'ninja-photo-feed'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'ig_pagination_typography',
                'label' => __('Typography', 'ninja-photo-feed'),
                'selector' => '{{WRAPPER}} .npfeed_more',
            ]
        );

        $this->add_control(
            'ig_pagination_text_color',
            [
                'label' => __('Text Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed_more' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'ig_pagination_background',
                'label' => __( 'Background', 'ninja-photo-feed' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .npfeed_more',
            ]
        );

        $this->add_responsive_control(
            'ig_pagination_margin',
            [
                'label' => esc_html__('Margin', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed_more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ig_pagination_padding',
            [
                'label' => esc_html__('Padding', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed_more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'ig_pagination_border',
                'selector' => '{{WRAPPER}} .npfeed_more',
            ]
        );

        $this->add_control(
            'ig_pagination_border_radius',
            [
                'label' => esc_html__('Border Radius', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed_more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'ig_pagination_box_shadow',
                'selector' => '{{WRAPPER}} .npfeed_more',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_ig_box_style_controls(){

        $this->start_controls_section(
            'section_ig_box_style',
            [
                'label' => __('Item Box', 'ninja-photo-feed'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'ig_box_platform_color',
            [
                'label' => __('Platform Icon Color', 'ninja-photo-feed'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-icon svg path' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'ig_box_background',
                'label' => __( 'Background', 'ninja-photo-feed' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .npfeed-ig-post,{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info',
            ]
        );

        $this->add_responsive_control(
            'ig_box_margin',
            [
                'label' => esc_html__('Margin', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ig_box_padding',
            [
                'label' => esc_html__('Padding', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post .npfeed-ig-post-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'ig_box_border',
                'selector' => '{{WRAPPER}} .npfeed-ig-post',
            ]
        );

        $this->add_control(
            'ig_box_border_radius',
            [
                'label' => esc_html__('Border Radius', 'ninja-photo-feed'),
                'type' => Controls_Manager::DIMENSIONS,
                'separator' => 'before',
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .npfeed-ig-post' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'ig_box_box_shadow',
                'selector' => '{{WRAPPER}} .npfeed-ig-post',
            ]
        );

        $this->end_controls_section();
    }
    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
        extract($settings);
        if(!empty($instagram_template_list)){
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking for editor context, not processing sensitive form data
            $postId = absint(Arr::get($_REQUEST, 'editor_post_id'));
            if ($postId) {
                Helper::saveTemplateMeta($postId, 'ig');
            }
            echo do_shortcode('[ninja_photo_feed id="' . $instagram_template_list . '" platform="instagram"]');
        }
    }
}