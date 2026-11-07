<?php

namespace NinjaPhotoFeed\App\Http\Policies;

use NinjaPhotoFeed\Framework\Foundation\Policy;
use NinjaPhotoFeed\Framework\Http\Request\Request;
use NinjaPhotoFeed\App\Services\PermissionManager;


/**
 *  BasePolicy - REST API Permission Policy
 *
 * @package NinjaPhotoFeed\App\Http
 *
 * @version 1.0.0
 */
class BasePolicy extends Policy
{

    /**
     * Check user permission for any method
     * @param  \NinjaPhotoFeed\Framework\Http\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return $this->currentUserCan('manage_options');
    }

    public function currentUserCan($permission)
    {
        return PermissionManager::currentUserCan($permission);
    }
}
