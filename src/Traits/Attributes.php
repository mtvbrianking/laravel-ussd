<?php

namespace Bmatovu\Ussd\Traits;

use Illuminate\Support\Str;

trait Attributes
{
    /**
     * @see https://stackoverflow.com/q/413071/2732184
     * @see https://www.regextester.com/97707
     */
    public function translate(string $text, string $pattern = '/[^{{\}\}]+(?=}})/'): string
    {
        preg_match_all($pattern, $text, $matches);

        if (0 === \count($matches[0])) {
            return $text;
        }

        $replace_vars = [];

        foreach ($matches[0] as $match) {
            $var = Str::slug($match, '_');
            $replace_vars["{{{$match}}}"] = $this->cache->get("{$this->prefix}_{$var}", "{{$var}}");
        }

        return strtr($text, $replace_vars);
    }

    public function readAttr(string $name, $default = null)
    {
        $value = $this->node->attributes->getNamedItem($name)->nodeValue ?? $default;

        return $this->translate($value);
    }
}
