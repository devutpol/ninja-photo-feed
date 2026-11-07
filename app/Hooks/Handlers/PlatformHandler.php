<?php

namespace NinjaPhotoFeed\App\Hooks\Handlers;

use NinjaPhotoFeed\App\Services\Platforms\Feeds\Instagram\InstagramFeed;

class PlatformHandler
{
    public function register()
    {
        (new InstagramFeed())->registerHooks();
    }
}
