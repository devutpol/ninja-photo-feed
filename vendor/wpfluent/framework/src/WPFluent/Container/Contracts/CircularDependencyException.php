<?php

namespace NinjaPhotoFeed\Framework\Container\Contracts;

use Exception;
use NinjaPhotoFeed\Framework\Container\Contracts\Psr\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
