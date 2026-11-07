<?php

namespace NinjaPhotoFeed\App\Http\Controllers;

use NinjaPhotoFeed\App\Models\Post;
use NinjaPhotoFeed\App\Models\Template;
use NinjaPhotoFeed\Framework\Foundation\Application;
use NinjaPhotoFeed\Framework\Http\Request\Request;
use NinjaPhotoFeed\App\Services\PermissionManager;

class TemplatesController extends Controller
{
    protected $app = null;
    protected $postType = 'npfeed_template';

    // The type guard in Post::updatePost()/deletePost() allows these and rejects any other post.
    protected $managedPostTypes = [
        'npfeed_template',
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get all templates from posts table.
     */
    public function index(Request $request, Template $template, Post $post)
    {
        $search = sanitize_text_field($request->get('search', ''));
        $filter = sanitize_text_field($request->get('filter', ''));

        $templates = $post->getPosts(
            $this->postType,
            $search,
            $filter
        );

        // Instagram-only platforms
        $platforms = [
            'instagram' => __('Instagram', 'ninja-photo-feed')
        ];

        $validShortcodeType = [
            'instagram' => true
        ];

        return [
            'message'                     => 'success',
            'connected_platform_sections' => $validShortcodeType,
            'all_valid_platforms'         => $platforms,
            'items'                       => $templates,
            'total_items'                 => $templates->total()
        ];
    }

    /**
     * Create single template on posts table.
     */
    public function create(Request $request, Template $template, Post $post)
    {
        try {
            $platform = sanitize_text_field($request->get('platform', 'instagram'));
            $onboarding = rest_sanitize_boolean($request->get('onboarding', false));

            $postTitle = __('Instagram Template', 'ninja-photo-feed');

            $postId = $post->createPost(
                [
                    'post_title'   => $postTitle,
                    'post_content' => $platform,
                    'post_type'    => $this->postType,
                ]
            );

            $postMeta = $template->getPlatformDefaultConfig($platform);
            $postMeta['feed_settings']['created_from_onboarding'] = (bool) $onboarding;
            $post->updatePostMeta($postId, $postMeta, $platform);

            return [
                'template_id' => $postId
            ];
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ], 423);
        }
    }

    /**
     * Duplicate single template.
     */
    public function duplicate(Request $request, Post $post)
    {
        $ids = (array) $request->get('ids', []);
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return __('No templates selected', 'ninja-photo-feed');
        }

        $duplicatedCount = 0;
        $response = [];

        foreach ($ids as $id) {
            $template = Template::find($id);

            if (!$template) {
                continue;
            }

            $template['post_title'] = '(Duplicate) ' . $template['post_title'];

            $templateId = $post->createPost(
                [
                    'post_title'   => $template['post_title'],
                    'post_content' => $template['post_content'],
                    'post_type'    => $this->postType,
                ]
            );

            $templateConfig = get_post_meta($template['ID'], '_npfeed_template_config', true);
            $feed_template_style_meta = get_post_meta($template['ID'], '_npfeed_template_styles_config', true);

            if ($templateConfig) {
                update_post_meta($templateId, '_npfeed_template_config', $templateConfig);
            }

            if ($feed_template_style_meta) {
                update_post_meta($templateId, '_npfeed_template_styles_config', $feed_template_style_meta);
            }

            if (count($ids) === 1) {
                $response['item'] = get_post($templateId, 'ARRAY_A');
                $response['item_id'] = $templateId;
            }

            $duplicatedCount++;
        }

        $response['message'] = sprintf(
            // translators: %d is the number of template that were duplicated
            _n(
                '%d template has been successfully duplicated',
                '%d templates have been successfully duplicated',
                $duplicatedCount,
                'ninja-photo-feed'
            ),
            $duplicatedCount
        );

        return $response;
    }

    /**
     * True when the current user holds the capability owning this object's post type.
     */
    protected function userCanManageObject($postId)
    {
        return PermissionManager::currentUserCanManagePostType(get_post_type((int) $postId));
    }

    /**
     * Delete template.
     */
    public function delete(Request $request, Post $post)
    {
        $ids = (array) $request->get('ids', []);
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return __('No templates selected', 'ninja-photo-feed');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            if (!$this->userCanManageObject($id)) {
                continue;
            }

            if (!$post->deletePost($id, $this->managedPostTypes)) {
                continue;
            }
            $deletedCount++;
            do_action('ninjaphotofeed/template_deleted', $id);
        }

        return [
            'message' => sprintf(
                // translators: %d is the number of template that were deleted
                _n(
                    '%d template has been successfully deleted',
                    '%d templates have been successfully deleted',
                    $deletedCount,
                    'ninja-photo-feed'
                ),
                $deletedCount
            )
        ];
    }

    /**
     * Update template title.
     */
    public function updateTitle(Request $request, Post $post, $templateId)
    {
        try {
            if (!$this->userCanManageObject($templateId)) {
                wp_send_json_error([
                    'message' => __('You are not allowed to update this item', 'ninja-photo-feed')
                ], 403);
            }

            $templateTitle = sanitize_text_field($request->get('template_title', ''));

            $updateArgs = [
                'ID'         => $templateId,
                'post_title' => $templateTitle,
            ];
            $result = $post->updatePost($updateArgs, $this->managedPostTypes);
            if ($result === false) {
                wp_send_json_error([
                    'message' => __('Failed to update title', 'ninja-photo-feed')
                ], 422);
            }

            return [
                'message' => __("Title updated successfully!!", 'ninja-photo-feed'),
                'title'   => $templateTitle,
                'id'      => $templateId,
                'result'  => $result
            ];
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ], 423);
        }
    }
}
