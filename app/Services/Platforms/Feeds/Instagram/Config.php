<?php

namespace NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram;

use NinjaPhotoFeed\Framework\Support\Arr;

if (!defined('ABSPATH')) {
    exit;
}

class Config
{
    public function __construct()
    {

    }

    public function getStyleElement()
    {
        $has_pro = defined('NINJAPHOTOFEED_PRO') && NINJAPHOTOFEED_PRO;

        return array(
            'header' => array(
                'title' => __('Header', 'ninja-photo-feed'),
                'key'  => 'header',
                'condition' => array(
	                'key' => 'header_settings.display_header',
	                'selector'  => 'true',
                ),
                array(
                    'title'     => __('Profile Photo', 'ninja-photo-feed'),
                    'key'      => 'profile_photo',
                    'divider' => true,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'condition' => array(
                        'key' => 'header_settings.header_layout',
                        'selector'  => 'minimal',
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Border Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('User Name', 'ninja-photo-feed'),
                    'key'      => 'user_name',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'slider' => array(
                        'title' => __('Bottom Spacing', 'ninja-photo-feed'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Statistics', 'ninja-photo-feed'),
                    'key'      => 'account_statistics_number',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Number Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'key'      => 'account_statistics',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'slider' => array(
                        'title' => __('Spacing Between Item', 'ninja-photo-feed'),
                    ),
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Full Name', 'ninja-photo-feed'),
                    'key'      => 'full_name',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Description', 'ninja-photo-feed'),
                    'key'      => 'account_description',
                    'divider' => true,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Follow Button', 'ninja-photo-feed'),
                    'key'      => 'follow_button',
                    'divider' => true,
                    'typography' => true,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Button Background Color', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Follow Button Hover Style', 'ninja-photo-feed'),
                    'key'      => 'follow_button_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Border Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Header Box', 'ninja-photo-feed'),
                    'key'      => 'account_info_wrapper',
                    'divider' => false,
                    'typography' => false,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'box_shadow' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'content' => array(
                'title' => __('Content', 'ninja-photo-feed'),
                'key'  => 'content',
                'condition' => array(
	                'key' => 'post_settings.display_caption',
	                'selector'  => 'true',
                ),
                array(
                    'key'      => 'hashtag',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Hashtag Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'key'      => 'content',
                    'divider' => false,
                    'typography' => true,
                    'padding' => true,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'statistics' => array(
                'title' => __('Statistics', 'ninja-photo-feed'),
                'key'  => 'statistics',
                array(
                    'key'      => 'statistics_icon',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Icon Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'fill_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'key'      => 'statistics',
                    'divider' => false,
                    'typography' => true,
                    'padding' => true,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'key'      => 'statistics_wrapper',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'slider' => array(
                        'title' => __('Spacing Between Item', 'ninja-photo-feed'),
                    ),
                ),
            ),
            'popup' => array(
                'title' => __('Popup Box', 'ninja-photo-feed'),
                'key'  => 'popup_box',
                'condition' => array(
	                'key' => 'post_settings.display_mode',
	                'selector'  => 'popup',
	                'operator'   => '=='
                ),
                array(
                    'title'     => __('User Name', 'ninja-photo-feed'),
                    'key'      => 'popup_user_info_name',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Post Description', 'ninja-photo-feed'),
                    'key'      => 'popup_post_description',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
	            array(
		            'title'     => __('Post Hashtag', 'ninja-photo-feed'),
		            'key'      => 'popup_post_hashtag',
		            'divider' => false,
		            'typography' => true,
		            'padding' => false,
		            'border' => false,
		            'styles' => array(
			            array(
				            'title'      => __('Text Color:', 'ninja-photo-feed'),
				            'fieldKey'  => 'text_color',
				            'type'      => 'color_picker',
				            'flex'      => true,
				            'disabled' => !$has_pro,
			            )
		            )
	            ),
                array(
                    'title'     => __('Post Time', 'ninja-photo-feed'),
                    'key'      => 'popup_post_time',
                    'divider' => false,
                    'typography' => true,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Call to action', 'ninja-photo-feed'),
                    'key'      => 'popup_call_to_action',
                    'divider' => false,
                    'typography' => true,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                ),
            ),
            'pagination' => array(
                'title' => __('Pagination', 'ninja-photo-feed'),
                'key'  => 'pagination',
                'condition' => array(
	                'key' => 'pagination_settings.pagination_type',
	                'selector'  => 'none',
	                'operator'   => '!='
                ),
                array(
                    'key'      => 'pagination',
                    'divider' => false,
                    'typography' => true,
                    'slider' => array(
                        'title' => __('Top Spacing', 'ninja-photo-feed'),
                    ),
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                    )
                ),
                array(
                    'title'     => __('Hover Style', 'ninja-photo-feed'),
                    'key'      => 'pagination_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Border Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'footer' => array(
                'title' => __('Footer Follow Button', 'ninja-photo-feed'),
                'key'  => 'footer_follow_button',
                'condition' => array(
                    'key' => 'follow_button_settings.display_follow_button',
                    'selector'  => 'true',
                ),
                array(
                    'key'      => 'footer_follow_button',
                    'divider' => true,
                    'typography' => true,
                    'slider' => array(
                        'title' => __('Top Spacing', 'ninja-photo-feed'),
                    ),
                    'padding' => true,
                    'border' => true,
                    'border_radius' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Text Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Button Background Color', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
                array(
                    'title'     => __('Hover Style', 'ninja-photo-feed'),
                    'key'      => 'footer_follow_button_hover',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Text Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'text_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Background Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        ),
                        array(
                            'title'      => __('Border Hover Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'border_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'content_box' => array(
                'title' => __('Item Content Box', 'ninja-photo-feed'),
                'key'  => 'content_box',
                array(
                    'key'      => 'content_box',
                    'divider' => false,
                    'typography' => false,
                    'padding' => true,
                    'border' => true,
                    'border_radius' => false,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
            'item_box' => array(
                'title' => __('Item Box', 'ninja-photo-feed'),
                'key'  => 'item_box',
                array(
                    'key'      => 'item_box',
                    'divider' => false,
                    'typography' => false,
                    'padding' => false,
                    'border' => true,
                    'border_radius' => true,
                    'box_shadow' => true,
                    'styles' => array(
                        array(
                            'title'      => __('Background Color:', 'ninja-photo-feed'),
                            'fieldKey'  => 'background_color',
                            'type'      => 'color_picker',
                            'flex'      => true,
                            'disabled' => !$has_pro,
                        )
                    )
                ),
            ),
        );
    }

    public function formatStylesConfig($settings = [], $postId = null)
    {
        $prefix = '.npfeed-ig-feed-template-'.$postId;
        $popupPrefix = '.npfeed-ig-feed-popup-box-'.$postId;
        return [
            'styles' => array(
                'profile_photo' => array(
                    // Scoped to the minimal layout — that is the only header that rings the avatar.
                    'selector' => $prefix . ' .npfeed-ig-header.npfeed-ig-header-layout-minimal .npfeed-ig-header-inner .npfeed-ig-header-logo a img',
                    'color'  => array(
                        'border_color' => Arr::get($settings,'styles.profile_photo.color.border_color', '')
                    )
                ),
                'user_name' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-name a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.user_name.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.user_name.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.user_name.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.user_name.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.user_name.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.user_name.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.user_name.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.user_name.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.user_name.slider.bottom.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.user_name.slider.bottom.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.user_name.slider.bottom.mobile', 0),
                        ),
                    ),
                ),
                'account_statistics_number' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-statistics .npfeed-ig-header-statistic-item strong',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.account_statistics_number.color.text_color', '')
                    ),
                ),
                'account_statistics' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-statistics .npfeed-ig-header-statistic-item',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.account_statistics.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.account_statistics.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_statistics.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_statistics.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.account_statistics.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_statistics.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_statistics.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.account_statistics.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_statistics.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_statistics.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.account_statistics.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.account_statistics.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.account_statistics.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.account_statistics.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.account_statistics.slider.right.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.account_statistics.slider.right.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.account_statistics.slider.right.mobile', 0),
                        ),
                    ),
                ),
                'full_name' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-fullname',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.full_name.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.full_name.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.full_name.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.full_name.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.full_name.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.full_name.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.full_name.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.full_name.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.full_name.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.full_name.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.full_name.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.full_name.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.full_name.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.full_name.typography.text_decoration', ''),
                    ),
                ),
                'account_description' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner .npfeed-ig-header-info .npfeed-ig-header-description p',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.account_description.color.text_color', '')
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.account_description.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_description.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_description.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.account_description.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_description.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_description.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.account_description.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_description.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_description.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.account_description.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.account_description.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.account_description.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.account_description.typography.text_decoration', ''),
                    ),
                ),
                'follow_button' => array(
                    'selector' => $prefix . ' .npfeed-ig-header-inner .npfeed-ig-follow-btn a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.follow_button.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.follow_button.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.follow_button.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.follow_button.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.follow_button.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.follow_button.typography.text_decoration', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.follow_button.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.follow_button.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.follow_button.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.follow_button.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.follow_button.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.follow_button.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.follow_button.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.follow_button.border_radius.linked', false),
                    ),
                ),
                'follow_button_hover' => array(
                    'selector' => $prefix . ' .npfeed-ig-header-inner .npfeed-ig-follow-btn a:hover',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.follow_button_hover.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.follow_button_hover.color.background_color', ''),
                        'border_color' => Arr::get($settings,'styles.follow_button_hover.color.border_color', ''),
                    ),
                ),
                'account_info_wrapper' => array(
                    'selector' => $prefix . ' .npfeed-ig-header .npfeed-ig-header-inner',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.account_info_wrapper.color.background_color', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.account_info_wrapper.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.account_info_wrapper.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.account_info_wrapper.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.account_info_wrapper.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.account_info_wrapper.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.account_info_wrapper.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.account_info_wrapper.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.account_info_wrapper.border_radius.linked', false),
                    ),
                    'box_shadow' => array(
                        'box_shadow_style' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.box_shadow_style', 'none'),
                        'horizontal' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.horizontal', ''),
                        'vertical' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.vertical', ''),
                        'blur' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.blur', ''),
                        'spread' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.spread', ''),
                        'color' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.color', ''),
                        'inset' => Arr::get($settings,'styles.account_info_wrapper.box_shadow.inset', 'no'),
                    ),
                ),
                'content' => array(
                    // Template 4's public markup ships in the Pro plugin, so both its pre- and
                    // post-rebuild caption structures are listed; the unused one simply matches nothing.
                    'selector' => $prefix . ' .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p, ' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-hover .npfeed-ig-post-pin-hover-content .npfeed-ig-post-caption p, ' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-caption .npfeed-ig-post-caption p, ' . $prefix . ' .npfeed-ig-post-overlay-card .npfeed-ig-post-overlay-hover .npfeed-ig-post-overlay-hover-content .npfeed-ig-post-caption p',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.content.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size'  => array(
                            'desktop' => Arr::get($settings,'styles.content.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.content.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.content.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.content.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.content.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.content.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.content.typography.text_decoration', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.content.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.content.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.content.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.content.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.content.padding.linked', false),
                    ),
                ),
                'hashtag' => array(
                    // Template 4's public markup ships in the Pro plugin, so both its pre- and
                    // post-rebuild caption structures are listed; the unused one simply matches nothing.
                    'selector' => $prefix . ' .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-caption p a, ' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-hover .npfeed-ig-post-pin-hover-content .npfeed-ig-post-caption p a, ' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-caption .npfeed-ig-post-caption p a, ' . $prefix . ' .npfeed-ig-post-overlay-card .npfeed-ig-post-overlay-hover .npfeed-ig-post-overlay-hover-content .npfeed-ig-post-caption p a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.hashtag.color.text_color', ''),
                    ),
                ),
                'statistics' => array(
                    'selector' => $prefix . ' .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic span,' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-hover .npfeed-ig-post-pin-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic span,' . $prefix . ' .npfeed-ig-post-overlay-card .npfeed-ig-post-overlay-hover .npfeed-ig-post-overlay-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic span',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.statistics.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.statistics.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.statistics.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.statistics.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.statistics.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.statistics.typography.text_decoration', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.statistics.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.statistics.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.statistics.padding.linked', false),
                    ),
                    'slider'  => array(
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.statistics.slider.right.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.statistics.slider.right.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.statistics.slider.right.mobile', 0),
                        ),
                    ),
                ),
                'statistics_icon' => array(
                    'selector' => $prefix . ' .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic svg,' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-hover .npfeed-ig-post-pin-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic svg,' . $prefix . ' .npfeed-ig-post-overlay-card .npfeed-ig-post-overlay-hover .npfeed-ig-post-overlay-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic svg',
                    'color'  => array(
                        'fill_color' => Arr::get($settings,'styles.statistics_icon.color.fill_color', ''),
                    )
                ),
                'statistics_wrapper' => array(
                    'selector' => $prefix . ' .npfeed-ig-post .npfeed-ig-post-info .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic,' . $prefix . ' .npfeed-ig-post-pin-card .npfeed-ig-post-pin-hover .npfeed-ig-post-pin-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic,' . $prefix . ' .npfeed-ig-post-overlay-card .npfeed-ig-post-overlay-hover .npfeed-ig-post-overlay-hover-content .npfeed-ig-post-statistics .npfeed-ig-post-single-statistic',
                    'slider'  => array(
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.statistics_wrapper.slider.right.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.statistics_wrapper.slider.right.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.statistics_wrapper.slider.right.mobile', 0),
                        ),
                    ),
                ),
                'popup_user_info_name' => array(
                    'selector' => $popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-user-name a,' .$popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-comments-wrapper .npfeed-feed-popup-comment-text > a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.popup_user_info_name.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.popup_user_info_name.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_user_info_name.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_user_info_name.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_user_info_name.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_user_info_name.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_user_info_name.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_user_info_name.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_user_info_name.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_user_info_name.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.popup_user_info_name.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.popup_user_info_name.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.popup_user_info_name.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.popup_user_info_name.typography.text_decoration', ''),
                    )
                ),
                'popup_post_hashtag' => array(
	                'selector' => $popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-comments-wrapper .npfeed-feed-popup-comment-text > * a',
	                'color'  => array(
		                'text_color' => Arr::get($settings,'styles.popup_post_hashtag.color.text_color', ''),
	                ),
	                'typography' => array(
		                'font_size' => array(
			                'desktop' => Arr::get($settings,'styles.popup_post_hashtag.typography.font_size.desktop', ''),
			                'tablet' => Arr::get($settings,'styles.popup_post_hashtag.typography.font_size.tablet', ''),
			                'mobile' => Arr::get($settings,'styles.popup_post_hashtag.typography.font_size.mobile', ''),
		                ),
		                'letter_spacing'  => array(
			                'desktop' => Arr::get($settings,'styles.popup_post_hashtag.typography.letter_spacing.desktop', ''),
			                'tablet' => Arr::get($settings,'styles.popup_post_hashtag.typography.letter_spacing.tablet', ''),
			                'mobile' => Arr::get($settings,'styles.popup_post_hashtag.typography.letter_spacing.mobile', ''),
		                ),
		                'line_height'  => array(
			                'desktop' => Arr::get($settings,'styles.popup_post_hashtag.typography.line_height.desktop', ''),
			                'tablet' => Arr::get($settings,'styles.popup_post_hashtag.typography.line_height.tablet', ''),
			                'mobile' => Arr::get($settings,'styles.popup_post_hashtag.typography.line_height.mobile', ''),
		                ),
		                'font_weight'  => Arr::get($settings,'styles.popup_post_hashtag.typography.font_weight', ''),
		                'font_style'  => Arr::get($settings,'styles.popup_post_hashtag.typography.font_style', ''),
		                'text_transform'  => Arr::get($settings,'styles.popup_post_hashtag.typography.text_transform', ''),
		                'text_decoration'  => Arr::get($settings,'styles.popup_post_hashtag.typography.text_decoration', ''),
	                )
                ),
                'popup_post_description' => array(
                    'selector' => $popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-comments-wrapper .npfeed-feed-popup-comment-text p',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.popup_post_description.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_description.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_description.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_description.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_description.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_description.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_description.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_description.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_description.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_description.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.popup_post_description.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.popup_post_description.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.popup_post_description.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.popup_post_description.typography.text_decoration', ''),
                    )
                ),
                'popup_post_time' => array(
                    'selector' => $popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-comments-wrapper .npfeed-feed-popup-comment-meta time,' .$popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-statistics-date a' ,
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.popup_post_time.color.text_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_time.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_time.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_time.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_time.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_time.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_time.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_post_time.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_post_time.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_post_time.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.popup_post_time.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.popup_post_time.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.popup_post_time.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.popup_post_time.typography.text_decoration', ''),
                    )
                ),
                'popup_call_to_action' => array(
                    'selector' => $popupPrefix . ' .npfeed-feed-popup-box-wraper .npfeed-feed-popup-box-wraper-inner .npfeed-feed-popup-box-content .npfeed-feed-popup-view-post-cta a.npfeed-popup-shoppable-btn' ,
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.popup_call_to_action.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.popup_call_to_action.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.popup_call_to_action.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.popup_call_to_action.typography.text_decoration', ''),
                    ),
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.popup_call_to_action.color.text_color', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.popup_call_to_action.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.popup_call_to_action.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.popup_call_to_action.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.popup_call_to_action.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.popup_call_to_action.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.popup_call_to_action.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.popup_call_to_action.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.popup_call_to_action.border_radius.linked', false),
                    ),
                ),
                'pagination' => array(
                    'selector' => $prefix . ' .npfeed_more',
                    'color'  => array(
                        'color' => Arr::get($settings,'styles.pagination.color.color', ''),
                        'background_color' => Arr::get($settings,'styles.pagination.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.pagination.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.pagination.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.pagination.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.pagination.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.pagination.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.pagination.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.slider.top.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.pagination.slider.top.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.pagination.slider.top.mobile', 0),
                        ),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.pagination.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.pagination.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.pagination.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.pagination.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.pagination.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.pagination.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.pagination.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.pagination.border_radius.linked', false),
                    ),

                ),
                'pagination_hover' => array(
                    'selector' => $prefix . ' .npfeed_more:hover',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.pagination_hover.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.pagination_hover.color.background_color', ''),
                        'border_color' => Arr::get($settings,'styles.pagination_hover.color.border_color', ''),
                    ),
                ),
                'content_box' => array(
                    'selector' => $prefix . '.npfeed-ig-feed-wrapper .npfeed-ig-post-info',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.content_box.color.background_color', ''),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.content_box.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.content_box.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.content_box.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.content_box.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.content_box.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.content_box.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.content_box.border.border_color', ''),
                    ),
                ),
                'item_box' => array(
                    'selector' => $prefix . '.npfeed-ig-feed-wrapper .npfeed-ig-post',
                    'color'  => array(
                        'background_color' => Arr::get($settings,'styles.item_box.color.background_color', ''),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.item_box.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.item_box.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.item_box.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.item_box.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.item_box.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.item_box.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.item_box.border_radius.linked', false),
                    ),
                    'box_shadow' => array(
                        'box_shadow_style' => Arr::get($settings,'styles.item_box.box_shadow.box_shadow_style', 'none'),
                        'horizontal' => Arr::get($settings,'styles.item_box.box_shadow.horizontal', ''),
                        'vertical' => Arr::get($settings,'styles.item_box.box_shadow.vertical', ''),
                        'blur' => Arr::get($settings,'styles.item_box.box_shadow.blur', ''),
                        'spread' => Arr::get($settings,'styles.item_box.box_shadow.spread', ''),
                        'color' => Arr::get($settings,'styles.item_box.box_shadow.color', ''),
                        'inset' => Arr::get($settings,'styles.item_box.box_shadow.inset', 'no'),
                    ),
                ),
                'footer_follow_button' => array(
                    'selector' => $prefix . ' .npfeed-ig-footer .npfeed-ig-follow-btn a',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.footer_follow_button.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.footer_follow_button.color.background_color', ''),
                    ),
                    'typography' => array(
                        'font_size' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.typography.font_size.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.typography.font_size.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.typography.font_size.mobile', ''),
                        ),
                        'letter_spacing'  => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.typography.letter_spacing.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.typography.letter_spacing.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.typography.letter_spacing.mobile', ''),
                        ),
                        'line_height'  => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.typography.line_height.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.typography.line_height.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.typography.line_height.mobile', ''),
                        ),
                        'font_weight'  => Arr::get($settings,'styles.footer_follow_button.typography.font_weight', ''),
                        'font_style'  => Arr::get($settings,'styles.footer_follow_button.typography.font_style', ''),
                        'text_transform'  => Arr::get($settings,'styles.footer_follow_button.typography.text_transform', ''),
                        'text_decoration'  => Arr::get($settings,'styles.footer_follow_button.typography.text_decoration', ''),
                    ),
                    'slider'  => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.slider.top.desktop', 0),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.slider.top.tablet', 0),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.slider.top.mobile', 0),
                        ),
                    ),
                    'padding' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.padding.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.padding.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.padding.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.padding.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.padding.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.padding.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.padding.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.padding.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.padding.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.padding.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.padding.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.padding.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.footer_follow_button.padding.linked', false),
                    ),
                    'border' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.footer_follow_button.border.linked', false),
                        'border_style' => Arr::get($settings,'styles.footer_follow_button.border.border_style', ''),
                        'border_color' => Arr::get($settings,'styles.footer_follow_button.border.border_color', ''),
                    ),
                    'border_radius' => array(
                        'top' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border_radius.top.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border_radius.top.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border_radius.top.mobile', ''),
                        ),
                        'right' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border_radius.right.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border_radius.right.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border_radius.right.mobile', ''),
                        ),
                        'bottom' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border_radius.bottom.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border_radius.bottom.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border_radius.bottom.mobile', ''),
                        ),
                        'left' => array(
                            'desktop' => Arr::get($settings,'styles.footer_follow_button.border_radius.left.desktop', ''),
                            'tablet' => Arr::get($settings,'styles.footer_follow_button.border_radius.left.tablet', ''),
                            'mobile' => Arr::get($settings,'styles.footer_follow_button.border_radius.left.mobile', ''),
                        ),
                        'linked' => Arr::get($settings,'styles.footer_follow_button.border_radius.linked', false),
                    ),
                ),
                'footer_follow_button_hover' => array(
                    'selector' => $prefix . ' .npfeed-ig-footer .npfeed-ig-follow-btn a:hover',
                    'color'  => array(
                        'text_color' => Arr::get($settings,'styles.footer_follow_button_hover.color.text_color', ''),
                        'background_color' => Arr::get($settings,'styles.footer_follow_button_hover.color.background_color', ''),
                        'border_color' => Arr::get($settings,'styles.footer_follow_button_hover.color.border_color', ''),
                    ),
                ),
            ),
        ];
    }
}
