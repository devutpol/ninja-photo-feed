<?php

namespace NinjaPhotoFeed\Framework\Support\Facades;

use NinjaPhotoFeed\Framework\Support\Facade;

/**
 * Static calls to the view.
 *
 * @method static void render(string $path, array $data = [])
 * @method static string make(string $path, array $data = [])
 * @method static void share(string $key, mixed $value)
 * @method static \NinjaPhotoFeed\Framework\View\View with(string|array $name, mixed $data = [])
 *
 * @see \NinjaPhotoFeed\Framework\View\View
 */
class View extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}
