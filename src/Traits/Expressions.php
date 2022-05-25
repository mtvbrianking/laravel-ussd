<?php

namespace Bmatovu\Ussd\Traits;

use Illuminate\Support\Str;

trait Expressions
{
    protected function incExp(?string $exp, int $step = 1): ?string
    {
        if(! $exp) {
            return $exp;
        }

        return preg_replace_callback('|(\\d+)(?!.*\\d)|', function ($matches) use ($step) {
            return $matches[1] + $step;
        }, $exp);
    }

    /**
     * @see https://stackoverflow.com/q/413071/2732184
     * @see https://www.regextester.com/97707
     */
    protected function translate(string $text, string $pattern = '/[^{\}]+(?=})/'): string
    {
        preg_match_all($pattern, $text, $matches);

        if (0 === \count($matches[0])) {
            return $text;
        }

        $replace_vars = [];

        foreach ($matches[0] as $match) {
            $var = Str::slug($match, '_');
            $replace_vars["{{$match}}"] = $this->cache->get("{$this->prefix}_{$var}", "{{$var}}");
        }

        return strtr($text, $replace_vars);
    }
}
