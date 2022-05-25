<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\Tag;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

class ChooseTag implements Tag
{
    use Expressions;

    protected \DOMXPath $xpath;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    public function __construct(\DOMXPath $xpath, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->xpath = $xpath;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(\DOMNode $node): ?string
    {
        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp");

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $whenEls = $this->xpath->query('when', $node);

        $pos = 0;

        $isMatched = false;

        foreach ($whenEls as $idx => $whenEl) {
            $pos = $idx + 1;
            $key = $whenEl->attributes->getNamedItem('key')->nodeValue;
            $val = $whenEl->attributes->getNamedItem('value')->nodeValue;

            $var = $this->cache->get("{$this->prefix}_{$key}");

            if ($var !== $val) {
                continue;
            }

            $isMatched = true;

            $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
            $this->cache->put("{$this->prefix}_exp", "{$exp}/*[{$pos}]", $this->ttl);

            break;
        }

        if ($isMatched) {
            return '';
        }

        $otherwiseEl = $this->xpath->query('otherwise', $node)->item(0);

        if (! $otherwiseEl) {
            return '';
        }

        ++$pos;

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", "{$exp}/*[{$pos}]", $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[{$pos}]"]);

        return '';
    }

    public function process(\DOMNode $node, ?string $answer): void
    {
    }
}
