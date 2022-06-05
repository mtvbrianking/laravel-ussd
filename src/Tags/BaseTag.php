<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\CacheStore;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class BaseTag implements RenderableTag
{
    use Attributes;
    use CacheStore;
    use Expressions;

    protected \DOMNode $node;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    public function __construct(\DOMNode $node, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->node = $node;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(): ?string
    {
        return '';
    }
}
