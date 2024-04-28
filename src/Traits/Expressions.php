<?php

namespace Bmatovu\Ussd\Traits;

trait Expressions
{
    // Todo - handle decrement below position 1 edge case
    protected function decExp(?string $exp, int $step = 1): ?string
    {
        if (!$exp) {
            return $exp;
        }

        return preg_replace_callback('|(\\d+)(?!.*\\d)|', function ($matches) use ($step) {
            return $matches[1] - $step;
        }, $exp);
    }

    protected function incExp(?string $exp, int $step = 1): string
    {
        return preg_replace_callback('|(\\d+)(?!.*\\d)|', function ($matches) use ($step) {
            return $matches[1] + $step;
        }, $exp);
    }
}
