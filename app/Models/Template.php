<?php

namespace NinjaPhotoFeed\App\Models;

use NinjaPhotoFeed\App\Models\Traits\SearchableScope;
use NinjaPhotoFeed\App\Services\Platforms\PlatformManager;
use NinjaPhotoFeed\Framework\Support\Arr;

/**
 * Instagram-only version of core's Template. getPlatformDefaultConfig() dynamically dispatches
 * to "{$platform}TemplateConfig()", gated by PlatformManager::feedPlatforms() — trimmed to
 * ['instagram'] by item 7 — so twitter/youtube/facebookFeed/tiktokTemplateConfig() can never run.
 */
class Template extends Model
{
    use SearchableScope;

    protected static $type = 'npfeed_template';
    protected $table = 'posts';

    /**
     * $searchable Columns in table to search
     * @var array
     */
    protected $searchable = [
        'ID',
        'post_title',
        'post_content'
    ];

    public static function boot()
    {
        static::creating(function ($model) {
            $model->post_type   = static::$type;
            $model->post_status = 'publish';
        });

        static::addGlobalScope(function ($builder) {
            $builder->where('post_type', static::$type);
            $builder->where('post_status', 'publish');
        });
    }

    public function getPlatformDefaultConfig($platform)
    {
        $feedPlatforms = (new PlatformManager())->feedPlatforms();
        $config = [];
        if (in_array($platform, $feedPlatforms)) {
            $methodName   = $platform . 'TemplateConfig';
            $methodName   = str_replace('_feed', 'Feed', $methodName);
            $config = $this->$methodName();
        } else {
            if ($platform !== 'reviews') {
                $config = [
                    'platform' => array($platform)
                ];
            }
        }
        return $config;
    }

    public function instagramTemplateConfig()
    {
        $configs = get_option('npfeed_instagram_verification_configs');

        return [
            'feed_settings' => [
                'platform'        => 'instagram',
                'source_settings' => [
                    'feed_type'  => 'user_account_feed',
                    'account_id' => Arr::get($configs, 'account_id', '')
                ],
                'filters'         => [
                     'total_posts_number'  => array(
                        'desktop'  => 50,
                        'mobile'   => 50
                    ),
                ]
            ],
            'dynamic'       => []
        ];
    }
}
