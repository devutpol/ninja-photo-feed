<?php

namespace NinjaPhotoFeed\App\Services\Platforms;

use NinjaPhotoFeed\App\Models\OptimizeImage;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\CacheHandler;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\InstagramFeed;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\Common;
use NinjaPhotoFeed\App\Services\Helper as GlobalHelper;
use NinjaPhotoFeed\App\Services\Platforms\Feeds\Config;
use NinjaPhotoFeed\Framework\Support\Arr;

/**
 * Instagram-only version of core's ImageOptimizationHandler. Every method below is
 * trimmed to its 'instagram' branch; the other feed platforms this build doesn't ship
 * (facebook_feed, tiktok, twitter, youtube) are gone, including platformCoverPhoto(),
 * which only those platforms' branches called.
 */
class ImageOptimizationHandler extends BaseImageOptimizationHandler
{
    public $doneResizing = [];
    public $availableRecords = null;

    public $platform = '';
    public $image_format = 'jpg';

    public function __construct($platform)
    {
        parent::__construct($platform);
        $this->platform = $platform;
        $this->image_format = GlobalHelper::getOptimizeImageFormat();
    }

    public function registerHooks()
    {
        add_action('wp_ajax_npfeed_resize_images', array($this, 'savePhotos'));
        add_action('wp_ajax_nopriv_npfeed_resize_images', array($this, 'savePhotos'));
        add_action('ninjaphotofeed/check_instagram_access_token_validity_weekly', array($this, 'checkValidity'));
        add_action('ninjaphotofeed/reset_data', array($this, 'resetData'));
    }

    public function savePhotos()
    {
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';

        // Try frontend nonce first, then admin nonce
        $nonce_verified = wp_verify_nonce($nonce, 'npfeed-ajax-nonce') || wp_verify_nonce($nonce, 'ninja-photo-feed');

        if (!$nonce_verified) {
            wp_send_json_error([
                'message' => __('Security validation failed. Please try again', 'ninja-photo-feed')
            ], 423);
        }

        $id = absint(Arr::get($_REQUEST, 'id', -1));
        $platform = isset($_REQUEST['platform']) ? sanitize_text_field(wp_unslash($_REQUEST['platform'])) : '';
        $feed_type = isset($_REQUEST['feed_type']) ? sanitize_text_field(wp_unslash($_REQUEST['feed_type'])) : '';

        // The nonce is localized for logged-out visitors, so it authenticates nothing.
        // Gate on the template being publicly renderable before reading its meta, otherwise
        // an anonymous request could fetch a draft/private feed with the site's token and
        // materialize its private images into the uploads directory.
        if (!(new \NinjaPhotoFeed\App\Hooks\Handlers\ShortcodeHandler())->isPublicRenderableTemplate($id)) {
            wp_send_json_error(['message' => __('Invalid template.', 'ninja-photo-feed')], 403);
        }

        // Every feed platform registers this same action and wp_send_json_error()
        // ends the request, so only the handler being addressed may answer. Its
        // own optimization setting decides, not whichever template loaded the script.
        if ($this->platform === $platform && !$this->imageOptimizationEnabled($platform)) {
            wp_send_json_error([
                'message' => __('Image optimization is turned off.', 'ninja-photo-feed')
            ], 403);
        }

        if($id > 0 && $this->platform == $platform) {
            $encodedMeta   = get_post_meta($id, '_npfeed_template_config', true);
            $decodedMeta   = json_decode($encodedMeta, true);
            $feed_settings = Arr::get($decodedMeta, 'feed_settings', []);

            $formattedMeta = [];

            $feedConfigs = null;
            if($this->platform == 'instagram'){
                $formattedMeta = Config::formatInstagramConfig($feed_settings, array());
                $feedConfigs = (new InstagramFeed())->getTemplateMeta($formattedMeta);
            }

            $feeds = Arr::get($feedConfigs, 'dynamic.items', []);
            if($this->platform == 'instagram'){
                $feeds = array_map(function($feed) {
                    $oembed_image_failed = Arr::get($feed, 'oembed_image_failed', false);
                    if(!$oembed_image_failed){
                        return $feed;
                    }
                }, $feeds);
            }

            $resizedImages = Arr::get($feedConfigs, 'dynamic.resize_data', []);

            $photo_type = $feed_type ? $feed_type : Arr::get($feed_settings, 'source_settings.feed_type', '');
            $feedIds = [];
            foreach ($feeds as $index => $feed) {
                $max_records = $this->maxRecordsCount($this->platform);
                if($index > $max_records){
                    continue;
                }

                if($photo_type == 'album_feed'){
                    $photo_feeds = Arr::get($feed, 'photos.data', []);
                    $feedIds = array_merge($feedIds,array_column($photo_feeds , 'id'));
                    foreach ($photo_feeds as $itemFeed) {
                        $itemFeed['page_id'] = Arr::get($feed, 'page_id', '');
                        $itemFeed['media_type'] = 'IMAGE';
                        $itemFeed['default_media'] = Arr::get($feed, 'source', '');
                        $itemFeed['media_url'] = Arr::get($feed, 'source', '');
                        if (in_array(Arr::get($itemFeed, 'id'), $resizedImages)) {
                            $this->doneResizing[] = Arr::get($itemFeed, 'id');
                        } else {
                            if(!$this->maxResizingPerUnitTimePeriod()) {
                                if ($this->isMaxRecordsReached($this->platform)) {
                                    $this->deleteLeastUsedImages($this->platform);
                                }
                                $this->processSaveImage($itemFeed, $this->platform);
                            }
                        }
                    }
                } else {
                    $feedIds = array_column($feeds , 'id');
                    $feedId = Arr::get($feed, 'id');
                    if (in_array($feedId, $resizedImages)) {
                        $this->doneResizing[] = Arr::get($feed, 'id');
                    } else {
                        if (!$this->maxResizingPerUnitTimePeriod()) {
                            if ($this->isMaxRecordsReached($this->platform)) {
                                $this->deleteLeastUsedImages($this->platform);
                            }
                            $this->processSaveImage($feed, $this->platform);
                        }
                    }
                }
            }

            $header = Arr::get($feedConfigs, 'dynamic.header');
            $accountId = Arr::get($feedConfigs, 'feed_settings.header_settings.account_to_show');

            if (empty(Arr::get($header, 'user_avatar'))) {
                $accountId = null;
            }
            //get all connected ids

            $connected_ids = [];
            $account_ids = [];
            if ($this->platform == 'instagram') {
                $connected_ids            = (new Common())->findConnectedAccounts();
                // Formatted settings, so a template saved without account_ids (e.g. from onboarding) gets its default account.
                $account_ids = Arr::get($formattedMeta, 'feed_settings.source_settings.account_ids', []);
            }

            $connected_account_list = array_intersect_key($connected_ids, array_flip($account_ids));

            $account_id = null;
            foreach ($connected_account_list as $item) {
                if ($this->platform == 'instagram') {
                    $account_id = $this->platformHeaderLogo($item, $item['user_id']);
                }
            }

            $resizedImages = [
                'images_data' => $feedIds,
                'account_id'  => $account_id
            ];

            echo wp_json_encode($resizedImages);
        }
    }

    public function platformHeaderLogo($header, $accountId)
    {
        $account_id = $accountId;
        if (empty(Arr::get($header, 'user_avatar')) && $this->platform == 'instagram') {
            $accountId = null;
        }

        if (!empty($accountId) && $this->platform == 'instagram') {
            if ($this->localHeaderExists($accountId, 'avatars')) {
                $accountId = null;
            }
        }

        $globalSettings = $this->getGlobalSettings();
        if (!empty($accountId)) {
            $userAvatar = null;
            if ($this->platform == 'instagram') {
                $userAvatar = Arr::get($header, 'user_avatar');
            }
            $res = false;
            $isLocalUrl = GlobalHelper::isLocalUrl($userAvatar);
            if(!$isLocalUrl){
                $res = $this->maybeLocalHeader($account_id, $userAvatar, $globalSettings,'avatars');
            }

            if (!$res) {
                return $accountId = null;
            }
        }

        return $accountId;
    }

    public function getMediaSource($post)
    {
        $media_urls   = [];
        if($this->platform == 'instagram'){
            $accountType = Arr::get($post, 'images') ? 'personal' : 'business';
            $thumbnail = Arr::get($post, 'images.thumbnail.url');
            $low_resolution = Arr::get($post, 'images.low_resolution.url');
            $standard_resolution = Arr::get($post, 'images.standard_resolution.url');

            if ($accountType === 'personal') {
                $media_urls['150'] = $thumbnail;
                $media_urls['320'] = $low_resolution;
                $media_urls['640'] = $standard_resolution;
            } else {
                $full_size    = $this->getMediaUrl($post);
                $media_urls['150'] = $full_size;
                $media_urls['320'] = $full_size;
                $media_urls['640'] = $full_size;
            }
        }
        return $media_urls;
    }

    public function getMediaUrl($post)
    {
        $media_type = Arr::get($post, 'media_type');
        $default_media = Arr::get($post, 'default_media');
        $media_name = Arr::get($post, 'media_name');
        $thumbnail_url = Arr::get($post, 'thumbnail_url');

        if ($this->platform == 'instagram') {
            $childrenMedia = Arr::get($post, 'children', []);
            if ($media_type == 'IMAGE' && !empty($default_media) && $media_name != 'VIDEO') {
                if (!$this->isValidImageUrl($default_media) && $childrenMedia) {
                    return Arr::get($post, 'children.data.0.thumbnail_url', '');
                }
                return $default_media;
            }
            if($media_name == 'VIDEO' && !empty($thumbnail_url)) {
                return $thumbnail_url;
            }
        }
    }

    public function checkValidity($account)
    {
       $error_status = Arr::get($account, 'status');
       $has_app_permission_error = Arr::get($account, 'has_app_permission_error', false);
       if($error_status === 'error' && $has_app_permission_error){
           (new PlatformData($this->platform))->handleAppPermissionError();
       }
    }

    public function cleanData($account)
    {
        $userName   = Arr::get($account, 'username');
        $userId   = Arr::get($account, 'user_id');
        $image_id = '';
        if($this->platform == 'instagram'){
            $image_id = Arr::get($account, 'user_id');
        }

        $cacheHandler = new CacheHandler($this->platform);
        if(!empty($userName)) {
            (new OptimizeImage())->deleteMediaByUserName($userName, $this->platform);
            $uploadDir = $this->getUploadDir($this->platform) . '/' . $userName;
            $cacheHandler->clearCacheByAccount($userId);
            $this->deleteDirectory($uploadDir, $image_id);
        }
    }

    public function resetData($platform)
    {
        $connectedAccounts = [];
        if($platform == 'instagram'){
            $connectedIds      = get_option('npfeed_'.$platform.'_verification_configs', []);
            $connectedAccounts  = Arr::get($connectedIds, 'connected_accounts', []);
        }

        delete_option('npfeed_'.$platform.'_local_avatars');
        delete_option('npfeed_'.$platform.'_local_covers');

        foreach($connectedAccounts as $account) {
            $userName = '';
            $image_id = '';
            if($platform == 'instagram'){
                $userName   = Arr::get($account, 'username', '');
                $image_id = Arr::get($account, 'user_id', null);
            }
            if (!empty($account) && $this->platform === $platform) {
                (new OptimizeImage())->deleteMediaByUserName($userName, $platform);
                $uploadDir = $this->getUploadDir($platform) . '/' . $userName;
                $this->deleteDirectory($uploadDir, $image_id);
            }
        }
    }

    public function deleteDirectory($dir, $image_id)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $this->deleteDirectoryContents($dir);
        $this->deleteImagesOutside($dir, $image_id);

        return true;
    }

    private function deleteDirectoryContents($dir)
    {
        foreach (glob($dir . '/*') as $item) {
            if (is_dir($item)) {
                $this->deleteDirectoryContents($item);
                // Use WordPress filesystem method instead of direct rmdir()
                $wp_filesystem = $this->getWpFilesystem();
                if ($wp_filesystem) {
                    $wp_filesystem->rmdir($item);
                }
            } else {
                wp_delete_file($item);
            }
        }

        // Optionally remove root dir too — only if you intend to delete the root folder
        $wp_filesystem = $this->getWpFilesystem();
        if ($wp_filesystem) {
            $wp_filesystem->rmdir($dir);
        }
    }

    private function deleteImagesOutside($dir, $image_id)
    {
        $parentDir = dirname(rtrim($dir, '/')) . '/';

        foreach (glob($parentDir . '*') as $item) {
            if (is_dir($item)) {
                continue;
            }

            $filename = pathinfo($item, PATHINFO_FILENAME);
            if (str_starts_with($filename, $image_id)) {
                wp_delete_file($item);
            }
        }
    }

    public function getResizeNeededImageLists($feeds = [], $feed_settings = [])
    {
        $ids = array_column($feeds , 'id');
        // Platforms without a per-item owner column fall through every branch below.
        $userNames = [];
        if($this->platform == 'instagram'){
            $userNames = array_column($feeds , 'username');
        }
        $resized_images = (new OptimizeImage())->getMediaIds($ids, $userNames, $this->platform);
        return array_unique($resized_images);
    }

    public function getGlobalSettings()
    {
        $globalSettings = get_option('npfeed_'.$this->platform.'_global_settings');
        return Arr::get($globalSettings, 'global_settings', []);
    }

    public function formattedData($header,$headerMeta)
    {
        $avatar = '';
        if ($this->platform == 'instagram'){
            $avatar = Arr::get($header, 'user_avatar');
        }

        $accountId = Arr::get($header, 'account_id');
        if($accountId < 0){
            $accountId = Arr::get($header, 'account_id');
        }

        $globalSettings = $this->getGlobalSettings();
        $isLocalHeaderExists = $this->localHeaderExists($accountId,$headerMeta);

        if(!empty($accountId) && !$isLocalHeaderExists && Arr::get($globalSettings, 'optimized_images') === 'false') {
            return $header;
        }else if($isLocalHeaderExists && Arr::get($globalSettings, 'optimized_images') !== 'false'){
            $this->platformHeaderLogo($header, $accountId);
        }
        if(!empty($avatar) && !empty($accountId) && $headerMeta == 'avatars') {
            $isLocalUrl = GlobalHelper::isLocalUrl($avatar);
            $header['local_avatar'] = !$isLocalUrl ? $this->maybeLocalHeader($accountId, $avatar, $globalSettings,$headerMeta) : false;
        }

        return $header;
    }

    public function maybeLocalHeader($userId, $profilePicture, $globalSettings,$headerMeta)
    {
        if ($this->localHeaderExists($userId,$headerMeta)) {
            return $this->getLocalHeaderUrl($userId,$headerMeta);
        }
        $checkLocalImage = get_option('npfeed_'.$this->platform.'_local_avatars');

        if ($this->shouldCreateLocalHeader($userId, $globalSettings,$headerMeta) && (empty($checkLocalImage) || !isset($checkLocalImage[$userId]) || !$checkLocalImage[$userId])) {
            $created = $this->createLocalHeader($userId, $profilePicture,$headerMeta);

            $this->updateLocalHeaderStatus($userId, $created,$headerMeta);

            if ($created) {
                return $this->getLocalHeaderUrl($userId,$headerMeta);
            }
        }

        return false;
    }

    public function localHeaderExists($userId, $headerMeta)
    {
        $avatars = get_option('npfeed_'.$this->platform.'_local_'.$headerMeta, array());
        return !empty(Arr::get($avatars, $userId));
    }

    public function getLocalHeaderUrl($userId, $headerMeta = '')
    {
        $checkLocalAvatar = get_option('npfeed_'.$this->platform.'_local_avatars');
        if (isset($checkLocalAvatar[$userId])) {
            return $checkLocalAvatar[$userId] ? ($this->getUploadUrl() . '/' . $userId . '.'. $this->image_format) : '';
        } else {
            return '';
        }
    }

    public function shouldCreateLocalHeader($userId, $globalSettings, $headerMeta = '')
    {
        if (Arr::get($globalSettings, 'optimized_images') === 'true' || Arr::get($globalSettings, 'global_settings.optimized_images') === 'true') {
            $avatars = get_option('npfeed_'.$this->platform.'_local_'.$headerMeta, array());
            return empty(Arr::get($avatars, $userId));
        }
        return false;
    }

    public function updateLocalHeaderStatus($userId, $status, $headerMeta = '')
    {
        $avatars = get_option('npfeed_'.$this->platform.'_local_'.$headerMeta, array());
        if(!empty($userId)) {
            $avatars[$userId] = $status;
            update_option('npfeed_'.$this->platform.'_local_'.$headerMeta, $avatars);
        }
    }

    public function createLocalHeader($userName, $fileName,$headerMeta)
    {
        if (empty($fileName)) {
            return false;
        }

        // The URL comes from a platform response, so it is fetched the same
        // guarded way as any other stored media instead of being opened directly.
        $media = RemoteMediaFetcher::fetch($fileName);
        if (!$media) {
            return false;
        }

        if($headerMeta == 'avatars'){
            $fullFileName = $this->getUploadDir($this->platform) . '/' . $userName . '.'. $this->image_format;
        }else{
            $fullFileName = $this->getUploadDir($this->platform) . '/' . $userName . '_cover.'. $this->image_format;
        }

        try {
            $imageEditor = wp_get_image_editor($media['file']);
            if (!is_wp_error($imageEditor)) {
                $resize = $this->getHeaderImageSize($headerMeta);
                $imageEditor->set_quality(80);
                $imageEditor->resize($resize, null);
                // save() returns WP_Error on failure, and any object is truthy,
                // so testing it directly would skip the fallback below.
                $saved_image = $imageEditor->save($fullFileName);
                if (!is_wp_error($saved_image) && $saved_image) {
                    return true;
                }
            }

            return $this->storeFetchedImage($media, $fullFileName);
        } finally {
            RemoteMediaFetcher::release($media);
        }
    }

    /**
     * Resize width for a header image. Filterable; a non-positive value keeps
     * the default.
     *
     * @param string $headerMeta 'avatars' or anything else, meaning the cover
     *
     * @return int
     */
    public function getHeaderImageSize($headerMeta)
    {
        $default = $headerMeta === 'avatars' ? 150 : 600;
        $size = (int) apply_filters(
            'ninjaphotofeed/optimized_header_image_size',
            $default,
            $headerMeta,
            $this->platform
        );

        return $size > 0 ? $size : $default;
    }

    public function maxRecordsCount($platform)
    {
        $maxRecordsMap = [
            'instagram' => NINJAPHOTOFEED_INSTAGRAM_MAX_RECORDS,
        ];

        return $maxRecordsMap[$platform] ?? 0;
    }

    public function isValidImageUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        // get_headers() opens whatever the URL points at, including the loopback
        // interface, so the address is cleared before any request goes out.
        if (!RemoteMediaFetcher::isSafeUrl($url)) {
            return false;
        }

        $response = wp_safe_remote_head($url, [
            'timeout'     => RemoteMediaFetcher::timeout(),
            'redirection' => 0,
        ]);

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $contentType = wp_remote_retrieve_header($response, 'content-type');
        if (is_array($contentType)) {
            $contentType = reset($contentType);
        }

        return is_string($contentType) && strpos($contentType, 'image') !== false;
    }

}
