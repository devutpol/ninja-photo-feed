<?php

namespace NinjaPhotoFeed\Framework\Container;

use Exception;
use NinjaPhotoFeed\Framework\Container\Contracts\Psr\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
