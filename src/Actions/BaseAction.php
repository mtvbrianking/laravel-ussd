<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\CacheStore;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class BaseAction implements RenderableTag
{
    use Attributes;
    use CacheStore;
    use Expressions;

    protected \DOMNode $node;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;
    protected bool $isAnswerable = false;

    public function __construct(\DOMNode $node, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->node = $node;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(): ?string
    {
        $this->shiftCursor();

        return '';
    }

    protected function shiftCursor(): void
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));
    }
}
