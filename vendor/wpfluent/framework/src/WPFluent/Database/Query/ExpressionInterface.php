<?php

namespace NinjaPhotoFeed\Framework\Database\Query;

use NinjaPhotoFeed\Framework\Database\BaseGrammar;

interface ExpressionInterface
{
    /**
     * Get the value of the expression.
     *
     * @param  \NinjaPhotoFeed\Framework\Database\BaseGrammar $grammar
     * @return string|int|float
     */
    public function getValue(BaseGrammar $grammar);
}
