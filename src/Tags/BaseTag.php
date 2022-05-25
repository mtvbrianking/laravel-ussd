<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\Tag;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

class BaseTag implements Tag
{
    use Expressions;

    protected \DOMnode $node;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    public function __construct(\DOMnode $node, CacheContract $cache, string $prefix, ?int $ttl = null)
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

    public function process(?string $answer): void
    {
    }
}
