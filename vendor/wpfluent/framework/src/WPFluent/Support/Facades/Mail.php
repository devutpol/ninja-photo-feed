<?php

namespace NinjaPhotoFeed\Framework\Support\Facades;

use NinjaPhotoFeed\Framework\Support\Facade;

/**
 * Static calls that start a new email: every call gets a fresh Mail, so
 * one email's recipients never carry over to the next.
 *
 * @method static \NinjaPhotoFeed\Framework\Support\Mail to(string|array $emails)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail from(string $email, string|null $name = null)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail cc(string|array $emails)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail bcc(string|array $emails)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail subject(string $subject)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail body(string $body)
 * @method static \NinjaPhotoFeed\Framework\Support\Mail view(string $templatePath, array $data = [])
 *
 * @see \NinjaPhotoFeed\Framework\Support\Mail
 */
class Mail extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mail';
    }
}
