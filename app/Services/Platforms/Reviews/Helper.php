<?php

namespace NinjaPhotoFeed\App\Services\Platforms\Reviews;

use NinjaPhotoFeed\Framework\Support\Arr;
use NinjaPhotoFeed\App\Services\GlobalSettings;

/**
 * Stub Reviews Helper for Ninja Photo Feed. Only the two methods still called survive:
 * getImageSettings() (ShortcodeHandler, BaseImageOptimizationHandler) and validPlatforms()
 * (PlatformController's GET /platforms/ route, which always gets no reviews platforms back).
 */
class Helper
{
    public static function getImageSettings($platform)
    {
        if (empty($platform)) {
            return [];
        }
        $global_settings = get_option('npfeed_' . $platform . '_global_settings');
        $advanceSettings = (new GlobalSettings())->getGlobalSettings('advance_settings');
        $has_gdpr = Arr::get($advanceSettings, 'has_gdpr', 'false');
        $image_format = Arr::get($advanceSettings, 'optimize_image_format', 'jpg');
        $optimized_images = $platform === 'reviews'
            ? Arr::get($advanceSettings, 'review_optimized_images', 'false')
            : Arr::get($global_settings, 'global_settings.optimized_images', 'false');
        return [
            'optimized_images' => $optimized_images,
            'has_gdpr'         => $has_gdpr,
            'image_format'     => $image_format,
        ];
    }

    public static function validPlatforms($platforms = array())
    {
        return [];
    }
}
