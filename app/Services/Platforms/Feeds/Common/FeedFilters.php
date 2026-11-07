<?php

namespace NinjaPhotoFeed\App\Services\Platforms\Feeds\Common;

use NinjaPhotoFeed\App\Services\GlobalSettings;
use NinjaPhotoFeed\App\Services\Helper as GlobalHelper;
use NinjaPhotoFeed\App\Services\Platforms\MediaManager;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\Platforms\ImageOptimizationHandler;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Instagram-only version of core's FeedFilters. $this->platform is always 'instagram' here (its
 * only caller is InstagramFeed.php), so every youtube/twitter/facebook_feed/tiktok branch is
 * dropped, including the ~45-line facebook_feed album/single_album_feed block.
 */
class FeedFilters
{
    private $platform;

    /**
     * Timestamp of a feed item
     *
     * @param array $feed
     * @param string|null $feed_type
     *
     * @return int|null
     * @since 1.3.0
     */
    public function getTimestamp(array $feed, $feed_type = null)
    {
        $date = Arr::get($feed, 'timestamp', null);

        if (!empty($date)) {
            $date = strtotime($date);
        }

        return $date;
    }

    /**
     * Sort, filter and trim a feed response per the template's filter settings
     *
     * @param string $platform platform slug
     * @param array $feed_settings feed settings holding the filters
     * @param array $response response
     *
     * @return array
     * @since 1.3.0
     *
     */
    public function filterFeedResponse($platform, $feed_settings, $response)
    {
        $filterSettings = Arr::get($feed_settings, 'filters', []);
        if (!empty($filterSettings) && !empty($response)) {
            $this->platform = $platform;
            $filterResponse = array();

            $header = Arr::get($response, 'header', []);
            $feeds  = Arr::get($response, 'items', []);

            $resizedImages = [];
            if ($this->platform === 'instagram') {
                $imageHandlerObj = new ImageOptimizationHandler($this->platform);
                $globalSettings = $imageHandlerObj->getGlobalSettings();
                if(Arr::get($globalSettings, 'optimized_images') === 'true') {
                    $resizedImages = $imageHandlerObj->getResizeNeededImageLists($feeds,  $feed_settings);
                    // Some feed types (e.g. youtube single video) send '' as the header.
                    if (is_array($header) && !empty($header)) {
                        $account_to_show = Arr::get($feed_settings, 'header_settings.account_to_show');
                        $header['account_id'] = $account_to_show;
                        $header['avatar'] = $imageHandlerObj->formattedData($header, 'avatars');
                        $header['covers'] = $imageHandlerObj->formattedData($header, 'covers');
                    }
                }
            }

            $feed_type = Arr::get($feed_settings, 'source_settings.feed_type');

            $postOrder = Arr::get($filterSettings, 'post_order', 'ascending');
            if (in_array($postOrder, ['ascending', 'descending', 'facebook_event_default'])) {
                $multiply = ($postOrder === 'ascending') ? -1 : 1;
                usort($feeds, function ($m1, $m2) use ($multiply, $feed_type, $postOrder) {
                    $timestamp1 = is_bool($m1) ? null : $this->getTimestamp($m1, $feed_type);
                    $timestamp2 = is_bool($m2) ? null : $this->getTimestamp($m2, $feed_type);

                    if ($feed_type === 'event_feed' && $postOrder === 'facebook_event_default') {
                        return apply_filters('ninjaphotofeed/facebook_event_feed_sorting', $timestamp1, $timestamp2);
                    }

                    // "ascending" puts the newest first, "descending" the oldest.
                    if ($timestamp1 !== null && $timestamp2 !== null) {
                        if($timestamp1 === $timestamp2) {
                            return 0;
                        }
                        if($timestamp1 < $timestamp2) {
                            return -1 * $multiply;
                        }

                        return 1 * $multiply;
                    }

                    // An undated item counts as the more recent one.
                    if ($timestamp1 !== null) {
                        return $multiply;
                    }

                    if ($timestamp2 !== null) {
                        return -$multiply;
                    }

                    return 0;
                });
            }

            $map = [];
            if ($this->platform === 'instagram') {
                if (($filterSettings['post_order'] === 'most_popular' || $filterSettings['post_order'] === 'least_popular')) {
                    $feeds = apply_filters('ninjaphotofeed/instagram_feeds_by_popularity', $feeds,
                        $filterSettings['post_order']);
                }

                $feeds = array_map(function ($value) {
                    if(isset($value['permalink'])) {
                        return $value;
                    }
                }, $feeds);

                $feeds = array_filter($feeds);
            }

            if ($filterSettings['post_order'] === 'random') {
                // Pro shuffles through this filter; without Pro, core shuffles.
                if (has_filter('ninjaphotofeed/feeds_by_random')) {
                    $feeds = apply_filters('ninjaphotofeed/feeds_by_random', $feeds);
                } else {
                    $feeds = $this->shuffleFeeds($feeds);
                }
            }

            $totalPosts = Arr::get($filterSettings, 'total_posts_number');
            $numOfPosts = wp_is_mobile() ? Arr::get($totalPosts, 'mobile') : Arr::get($totalPosts, 'desktop');

            $numOfPosts = apply_filters('ninjaphotofeed/responsive_post_count', $numOfPosts, $totalPosts);

            $includesWords = [];
            $excludesWords = [];
            $hidePostIds   = [];
            $hasHidePost   = false;

            if (!empty($filterSettings['includes_inputs'])) {
                $includesWords = array_map('trim', explode(",", $filterSettings['includes_inputs']));
            }
            if (!empty($filterSettings['excludes_inputs'])) {
                $excludesWords = array_map('trim', explode(",", $filterSettings['excludes_inputs']));
            }
            if (!empty($filterSettings['hide_posts_by_id'])) {
                $hidePostIds = array_map('trim', explode(",", $filterSettings['hide_posts_by_id']));
            }

            $totalPostsIterator = 0;
            $global_settings = get_option('npfeed_'.$this->platform.'_global_settings');
            $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');

            $mediaManager = null;
            $imageSettings = [];
            if ($this->platform === 'instagram') {
                $imageSettings = [
                    'optimized_images' => Arr::get($global_settings, 'global_settings.optimized_images', 'false'),
                    'has_gdpr' => Arr::get($advanceSettings, 'has_gdpr', "false")
                ];

                $imageSize = Arr::get($feed_settings, 'post_settings.resolution', 'full');
                $mediaManager = new MediaManager($resizedImages, $imageSettings, $imageSize, $this->platform);
            }

            $hasLatestPost = false;
            $postIds = [];
            foreach ($feeds as $index => $feed) {
                if ($totalPostsIterator >= ($numOfPosts)) {
                    break;
                }
                $id = Arr::get($feed, 'id');
                $postIds[] = $id;

                $text_description = '';
                $feed_id          = '';
                $text_title       = '';

                if ($this->platform === 'instagram') {
                    $text_description = Arr::get($feed, 'caption', '');
                    $feed_id          = Arr::get($feed, 'permalink', '');
                }

                if(!in_array($id, $resizedImages)) {
                    $hasLatestPost = true;
                }

                if(gettype($mediaManager) !== "NULL") {
                    $feed_type = Arr::get($feed_settings, 'source_settings.feed_type');
                    $media_type = $mediaManager->getMediaType($feed);
                    if($feed_type == 'album_feed') {
                        $child_feeds = Arr::get($feed, 'photos.data');
                        $child_feeds[0]['page_id'] = Arr::get($feed, 'page_id', '');
                        $media_url = $mediaManager->getMediaUri($child_feeds[0]);
                        foreach($child_feeds as $index=> $item)
                        {
                            $item['default_media'] = Arr::get($item, 'source', '');
                            $item['page_id'] = Arr::get($feed, 'page_id', '');
                            $item['media_url']  = $mediaManager->getMediaUri($item);
                            $feed['photos']['data'][$index] = $item;
                        }

                    } else {
                        $media_url = $mediaManager->getMediaUri($feed);
                    }

                    $default_media = $mediaManager->getPlatformRemoteUri($feed);
                    $thumbnail_url = $mediaManager->getThumbnailUrl($feed);

                    if(empty($thumbnail_url) && Arr::get($imageSettings, 'optimized_images') === 'true' && (Arr::get($feed, 'media_type') === 'VIDEO' || Arr::get($feed, 'children.data.0.media_type') === 'VIDEO')) {
                        continue;
                    }

                    $optimized_image = Arr::get($imageSettings, 'optimized_images');
                    $account_id = null;
                    $feed['has_carousel']  = (Arr::get($imageSettings, 'optimized_images') === 'false' && Arr::get($feed, 'media_type') === 'CAROUSEL_ALBUM');
                    $feed['media_name'] = Arr::get($feed, 'media_type');
                    $feed['media_type'] = $media_type;
                    $feed['media_url']  = $media_url;
                    $feed['thumbnail_url'] = $thumbnail_url;
                    $feed['default_media'] = $default_media;
                    if ($optimized_image == "true") {
                        $imageOptimizationObj =  new ImageOptimizationHandler($this->platform);
                        if ($this->platform == 'instagram') {
                            $account_id = Arr::get($feed, 'accountId', '');
                        }
                        $feed['user_avatar'] = $imageOptimizationObj->getLocalHeaderUrl( $account_id ,'avatars' );
                    }
                }

                $post_caption = ' ' . str_replace(array('+', '%0A'), ' ',
                        urlencode(str_replace(array('#', '@'), array(' HASHTAG', ' MENTION'),
                            strtolower($text_description)))) . ' ';
                // Use decoded text for filtering so fancy unicode can match
                $filter_text = urldecode($post_caption);

                $hasIncludeWord = false;
                $hasExcludeWord = false;
                if (!empty($includesWords)) {
                    // Pro does the word matching. Without Pro every post passes.
                    $hasIncludeWord = apply_filters('ninjaphotofeed/include_or_exclude_feed', true, $includesWords, $filter_text);

                    if(!empty($text_title)) {
                        $hasIncludeWord2 = apply_filters('ninjaphotofeed/include_or_exclude_feed', true, $includesWords,
                            $text_title);
                        $hasIncludeWord = $hasIncludeWord || $hasIncludeWord2;
                    }
                }

                if (!empty($excludesWords)) {
                    $hasExcludeWord = apply_filters('ninjaphotofeed/include_or_exclude_feed', false, $excludesWords, $filter_text);

                    if(!empty($text_title)) {
                        $hasExcludeWord2 = apply_filters('ninjaphotofeed/include_or_exclude_feed', false, $excludesWords,
                            $text_title);
                        $hasExcludeWord = $hasExcludeWord || $hasExcludeWord2;
                    }
                }

                // Hiding posts is Pro's filter; unfiltered, it would return the ID list and hide every post.
                if (!empty($hidePostIds) && has_filter('ninjaphotofeed/hide_feed')) {
                    $hasHidePost = apply_filters('ninjaphotofeed/hide_feed', $hidePostIds, $feed_id);
                }

                $isOk = false;
                if (!empty($excludesWords) && !empty($includesWords)) {
                    $isOk = $hasIncludeWord && !$hasExcludeWord;
                } elseif (!empty($includesWords)) {
                    $isOk = $hasIncludeWord;
                } else {
                    $isOk = !$hasExcludeWord;
                }

                if ($isOk) {
                    if ($this->platform === 'instagram' && !$hasHidePost) {
                        $post_type = Arr::get($filterSettings, 'post_type');
                        if ($post_type === 'all') {
                            $filterResponse[] = $feed;
                        } elseif (isset($feed['media_type']) && ($feed['media_type'] === "IMAGE" || $feed['media_type'] === 'CAROUSEL_ALBUM') && $feed['media_name'] !== "VIDEO" && $post_type === 'images') {
                            $filterResponse[] = $feed;
                        } elseif (isset($feed['media_type']) && ($feed['media_type'] === "VIDEO" || $feed['media_name'] === "VIDEO") && $post_type === 'videos' && Arr::get($feed, 'media_url')) {
                            $filterResponse[] = $feed;
                        }
                        $totalPostsIterator++;
                    }
                }
            }

            if(Arr::get($imageSettings, 'optimized_images') === 'true') {
                (new ImageOptimizationHandler($this->platform))->updateLastRequestedTime($postIds);
            }

            return [
                'header'        => $header,
                'items'         => $filterResponse,
                'resize_data'   => $resizedImages,
                'has_latest_post'  => $hasLatestPost,
            ];
        }

        return $response;
    }

    /**
     * Check if a string contains any of the filter words (case-insensitive, trimmed).
     *
     * @param string $haystack The string to search in.
     * @param array $words The list of words/hashtags to match.
     * @return bool True if any word is found, false otherwise.
     */
    private function filterStringByWords($haystack, $words)
    {
        $haystack = GlobalHelper::normalizeText($haystack);
        foreach ($words as $word) {
            $normalizedWord = GlobalHelper::normalizeText($word);
            if ($normalizedWord === '') {
                continue;
            }

            $position = function_exists('mb_stripos')
                ? mb_stripos($haystack, $normalizedWord, 0, 'UTF-8')
                : stripos($haystack, $normalizedWord, 0);

            if ($position !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Shuffle feeds, keeping their keys.
     *
     * @param array $feeds
     * @return array
     */
    private function shuffleFeeds($feeds)
    {
        if (!is_array($feeds) || count($feeds) < 2) {
            return $feeds;
        }

        $keys = array_keys($feeds);
        shuffle($keys);

        $shuffled = array();
        foreach ($keys as $key) {
            $shuffled[$key] = $feeds[$key];
        }

        return $shuffled;
    }
}
