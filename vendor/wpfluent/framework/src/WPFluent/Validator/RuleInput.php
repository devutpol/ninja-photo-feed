<?php

namespace NinjaPhotoFeed\Framework\Validator;

use NinjaPhotoFeed\Framework\Support\Fluent;

/**
 * The data a Rule::when() closure gets: read a field as a property, as an
 * array key or with get($key, $default).
 *
 * It extends Support\Fluent while that class is deprecated, so a closure
 * that type-hints Fluent keeps working; when Fluent is removed, this class
 * keeps the same methods.
 */
class RuleInput extends Fluent
{
    //
}
