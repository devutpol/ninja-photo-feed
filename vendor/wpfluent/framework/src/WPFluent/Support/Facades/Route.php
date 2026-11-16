<?php

namespace NinjaPhotoFeed\Framework\Support\Facades;

use NinjaPhotoFeed\Framework\Support\Facade;

/**
 * Static calls to the router.
 *
 * @method static \NinjaPhotoFeed\Framework\Http\Route get(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Route post(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Route put(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Route patch(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Route delete(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Route any(string $uri, mixed $handler)
 * @method static \NinjaPhotoFeed\Framework\Http\Group group(array|\Closure $attributes = [], \Closure|null $callback = null)
 * @method static \NinjaPhotoFeed\Framework\Http\Router prefix(string $prefix)
 * @method static \NinjaPhotoFeed\Framework\Http\Router name(string $name)
 * @method static \NinjaPhotoFeed\Framework\Http\Router namespace(string $ns)
 * @method static \NinjaPhotoFeed\Framework\Http\Router withPolicy(mixed $handler, string|null $method = null)
 * @method static \NinjaPhotoFeed\Framework\Http\Router before(mixed ...$middleware)
 * @method static \NinjaPhotoFeed\Framework\Http\Router after(mixed ...$middleware)
 * @method static \NinjaPhotoFeed\Framework\Http\Route|null getByName(string $name)
 *
 * @see \NinjaPhotoFeed\Framework\Http\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'router';
    }
}
