<?php

namespace Bmatovu\Ussd\Support;

class Arr
{
    /**
     * @see https://stackoverflow.com/a/173479/2732184
     */
    public static function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, \count($arr) - 1);
    }

    public static function keysDiff(array $required, array $given): array
    {
        if (self::isAssoc($given)) {
            $given = array_keys($given);
        }

        return array_diff($required, $given);
    }
}
