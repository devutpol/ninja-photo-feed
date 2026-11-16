<?php

namespace NinjaPhotoFeed\Framework\Support\Concerns;

use NinjaPhotoFeed\Framework\Support\Helper;

trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param  callable|null  $callback
     * @return $this|\NinjaPhotoFeed\Framework\Support\Proxies\HigherOrderTapProxy
     */
    public function tap($callback = null)
    {
        return Helper::tap($this, $callback);
    }
}
