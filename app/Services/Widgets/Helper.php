<?php

namespace NinjaPhotoFeed\App\Services\Widgets;


use NinjaPhotoFeed\App\Models\Template;

if (!defined('ABSPATH')) {
    exit;
}

class Helper
{
    public static function getTemplates($platforms)
    {
        if( count($platforms) > 1 ){
            $posts = Template::whereNotIn('post_content', $platforms)->orderBy('ID', 'desc')->get();
        } else {
            $posts = Template::whereIn('post_content', $platforms)->orderBy('ID', 'desc')->get();
        }

        $templates = array();

        if ($posts) {
            $templates[0] = esc_html__('Select a template', 'ninja-photo-feed');
            foreach ($posts as $post) {
                $templates[$post->ID] = $post->post_title .' (id-'.$post->ID.')';
            }
        } else {
            $templates[0] = esc_html__('Create a template first', 'ninja-photo-feed');
        }

        return $templates;
    }

    public static function saveTemplateMeta($postId, $platform)
    {
        $npfeed_elementor_ids = get_post_meta($postId, '_npfeed_elementor_ids', true);
        $npfeed_elementor_ids = is_array($npfeed_elementor_ids) ? $npfeed_elementor_ids : [];
        if(!in_array($platform, $npfeed_elementor_ids)){
            $npfeed_elementor_ids = array_merge($npfeed_elementor_ids, [$platform]);
            update_post_meta($postId, '_npfeed_elementor_ids', $npfeed_elementor_ids);
        }
    }
}