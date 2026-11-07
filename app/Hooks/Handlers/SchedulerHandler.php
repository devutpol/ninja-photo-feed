<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

use NinjaPhotoFeed\App\Models\OptimizeImage;
use NinjaPhotoFeed\App\Services\Maintenance;
use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\Platforms\PlatformManager;

class SchedulerHandler
{
    private $feed_platforms = ['instagram'];

    private $all_platforms = ['instagram'];

    public function handle()
    {
        $platforms = apply_filters('ninjaphotofeed/platforms', $this->all_platforms);
        foreach ($platforms as $platform) {
            $is_active = (new PlatformManager())->isActivePlatform($platform);
            if ($is_active) {
                if (in_array($platform, $this->feed_platforms)){
                    do_action('npfeed_' . $platform . '_feed_update');
                    if ($platform === 'instagram') {
                        do_action('npfeed_instagram_access_token_refresh_weekly');
                    }
                } else {
                    do_action('npfeed_' . $platform . '_reviews_update');
                }
            }
        }
    }

    public function processDailyTask()
    {
        foreach ($this->feed_platforms as $platform) {
            $is_active = (new PlatformManager())->isActivePlatform($platform);
            if ($is_active) {
                do_action('npfeed_'.$platform.'_send_email_report');
            }
        }
    }

    public function processWeekly()
    {
        (new Maintenance())->maybeProcessData();

        $is_active = (new PlatformManager())->isActivePlatform('instagram');
        if($is_active){
            $optimize_images_user_ids = (new OptimizeImage())->getUserIds();

            $configs            = get_option('npfeed_instagram_verification_configs', []);
            $connected_accounts = Arr::get($configs, 'connected_accounts', []);

            if(count($optimize_images_user_ids)) {
                foreach ($connected_accounts as $account) {
                    $userId = Arr::get($account, 'user_id');
                    if (!empty($userId) && in_array($userId, $optimize_images_user_ids)) {
                        //check this account is valid, if not delete all images and clear db
                        do_action('ninjaphotofeed/check_instagram_access_token_validity_weekly', $account);
                    }
                }
            }
        }

        do_action('ninjaphotofeed/regenerate_ai_summary_cache');
    }
}
