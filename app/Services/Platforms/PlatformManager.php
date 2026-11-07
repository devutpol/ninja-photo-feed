<?php
namespace NinjaPhotoFeed\App\Services\Platforms;

use NinjaPhotoFeed\Framework\Support\Arr;

/**
 * Instagram-only version of core's PlatformManager. getPlatformIcon(), the four
 * verification/account-config getters and getDemoTemplate() are dropped: nothing in this
 * build calls them (Ninja Photo Feed has no platform picker or demo-preview feature).
 */
class PlatformManager
{
    private $feed_platforms = ['instagram'];
    private $reviews_platforms = [];

    /**
     * Set all feed platform name.
     *
     * @return array
     */
    public function feedPlatforms()
    {
        return $this->feed_platforms;
    }

    /**
     *  Set all review platform name.
     *
     * @return array
     */
    public function reviewsPlatforms()
    {
        return apply_filters('ninjaphotofeed/reviews_platforms', $this->reviews_platforms);
    }

    public function getPlatformOfficialName($platform = '', $returnWithType = false)
    {
        if(empty($platform)){
            return;
        }

        $formattedPlatformName = str_replace( '_feed', '', ucfirst($platform) );
        $platformName = $platform === 'facebook' ? __('Facebook', 'ninja-photo-feed') : $formattedPlatformName;
        $platformType = $platform === 'facebook' ? __(' Reviews', 'ninja-photo-feed') : __(' Feed', 'ninja-photo-feed');

        if($returnWithType){
            $platform = $platformName.$platformType;
        }

        return $platform;
    }

    public function isActivePlatform($platform)
    {
        if(empty($platform)){
            return false;
        }

        if(in_array($platform, $this->feed_platforms)) {
            return get_option('npfeed_' . $platform . '_verification_configs');
        } else {
            // Handle custom platforms and special 'custom' platform
            if ($this->isCustomPlatform($platform) || $platform === 'custom') {
                return true;
            }
            return get_option('npfeed_reviews_' . $platform . '_settings');
        }
    }

    /**
     * Check if platform is a custom platform
     *
     * @param string $platform
     * @return bool
     */
    private function isCustomPlatform($platform)
    {
        $customValidPlatforms = get_option('npfeed_available_valid_platforms', []);
        return !empty($customValidPlatforms) && array_key_exists($platform, $customValidPlatforms);
    }

    // Core's version appends the 'reviews' alias so reset-type sweeps also cover review
    // platforms; this build has none, so the alias is dropped.
    public function getPlatformsListWithReviewAlias()
    {
        return $this->feedPlatforms();
    }

    public function getAccountIdsFromTemplate($platform, $templateConfig, $templateId)
    {
        $accountIds = [];

        if ($platform === 'instagram') {
            $accountIds = Arr::get($templateConfig, 'feed_settings.source_settings.account_ids', []);
        }

        return array_filter($accountIds);
    }

    public function getUserName($feed, $platform_name)
    {
        if ($platform_name === 'instagram') {
            return Arr::get($feed, 'username', '');
        }
        return Arr::get($feed, 'source_id', '');
    }
}
