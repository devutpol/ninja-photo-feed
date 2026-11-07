<?php

namespace NinjaPhotoFeed\App\Services;

/**
 * Stub DashboardNotices for Ninja Photo Feed.
 */
class DashboardNotices
{
    public function noticeTracker()
    {
        return false;
    }

    public function updateNotices($args = [])
    {
    }

    public function getNoticesStatus()
    {
        return false;
    }

    public function maybeDisplayOptIn()
    {
        return false;
    }

    public function getGoogleReconnectPlatforms()
    {
        return [];
    }

    public function maybeDisplayNewsletter()
    {
        return false;
    }

    public function updateNewsletter($args = [])
    {
        return '';
    }

    public function getActiveOffer()
    {
        return null;
    }

    public function getUpgradeButtonConfig()
    {
        return [];
    }

    public function getPlatformPromotion($platform)
    {
        return [];
    }
}
