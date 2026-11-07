<?php

namespace NinjaPhotoFeed\Framework\Foundation;

use NinjaPhotoFeed\Framework\Foundation\Exceptions\ForbiddenHttpException;

/**
 * @deprecated 2.12.0 Use \NinjaPhotoFeed\Framework\Foundation\Exceptions\ForbiddenHttpException
 *             instead. This shim remains so existing throw/catch sites keep
 *             working; it produces the same 403 response via Route.php's
 *             HttpException branch.
 */
class ForbiddenException extends ForbiddenHttpException
{
    //
}
