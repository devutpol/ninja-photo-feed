<?php

namespace NinjaPhotoFeed\App\Http\Policies;

use NinjaPhotoFeed\Framework\Http\Request\Request;

/**
 * Method names here are matched against SettingsController actions: one named after an
 * action gates that route alone and verifyRequest never runs for it. Only add one when
 * a route's owning capability really differs from the group's.
 */
class SettingsPolicy extends BasePolicy
{
    /**
     * Check user permission for any method
     * @param  \NinjaPhotoFeed\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return $this->currentUserCan('npfeed_feeds_platforms_settings')
            || $this->currentUserCan('npfeed_reviews_platforms_settings')
            || $this->currentUserCan('npfeed_shoppable_settings')
            || $this->currentUserCan('npfeed_translation_settings')
            || $this->currentUserCan('npfeed_license_settings')
            || $this->currentUserCan('npfeed_feeds_advance_settings')
            || $this->currentUserCan('npfeed_manage_qr_codes');
    }

    /**
     * Gates SettingsController::resetErrorLog. The error log lives on the Platforms
     * screen, so it uses that screen's capability.
     */
    public function resetErrorLog(Request $request)
    {
        return $this->currentUserCan('npfeed_manage_platforms');
    }
}
