<?php

namespace NinjaPhotoFeed\App\Http\Policies;

use NinjaPhotoFeed\Framework\Http\Request\Request;

class ShoppablePolicy extends BasePolicy
{
    /**
     * Check user permission for any method
     * @param  \NinjaPhotoFeed\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return $this->currentUserCan('npfeed_shoppable_settings');
    }

}
