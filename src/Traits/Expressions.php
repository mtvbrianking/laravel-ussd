<?php

namespace Bmatovu\Ussd\Traits;

trait Expressions
{
    protected function incExp(string $exp, int $step = 1): string
    {
        return preg_replace_callback('|(\\d+)(?!.*\\d)|', function ($matches) use ($step) {
            return $matches[1] + $step;
        }, $exp);
    }
}
