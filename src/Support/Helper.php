<?php

namespace Bmatovu\Ussd\Support;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Str;

class Helper
{
    public static function getDomElements(\DOMNodeList $nodeList, ?string $nodeName): array
    {
        $els = array_filter(iterator_to_array($nodeList), function ($node) use ($nodeName) {
            // return $node instanceof \DOMElement && ($nodeName && $node->nodeName === $nodeName);

            if (! $node instanceof \DOMElement) {
                return false;
            }

            if (! $nodeName) {
                return true;
            }

            return $node->nodeName === $nodeName;
        });

        return array_values($els);
    }

    /**
     * @see https://stackoverflow.com/q/413071/2732184
     * @see https://www.regextester.com/97707
     */
    public static function translate(CacheContract $cache, string $prefix, string $text, string $pattern = '/[^{\}]+(?=})/'): string
    {
        preg_match_all($pattern, $text, $matches);

        if (0 === \count($matches[0])) {
            return $text;
        }

        $replace_vars = [];

        foreach ($matches[0] as $match) {
            $var = Str::slug($match, '_');
            $replace_vars["{{$match}}"] = $cache->get("{$prefix}_{$var}", "{{$var}}");
        }

        return strtr($text, $replace_vars);
    }
}
