<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;

class IfTag extends BaseTag
{
    public function handle(): ?string
    {
        $key = $this->node->attributes->getNamedItem('key')->nodeValue;
        $value = $this->node->attributes->getNamedItem('value')->nodeValue;

        if ($this->cache->get("{$this->prefix}_{$key}") !== $value) {
            $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());

            $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
            $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);

            return '';
        }

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->cache->get("{$this->prefix}_breakpoints"), true);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", "{$exp}/*[1]", $this->ttl);

        $children = Helper::getDomElements($this->node->childNodes, null);
        $no_of_tags = \count($children);

        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);
        array_unshift($breakpoints, [$break => $this->incExp($exp)]);
        $this->cache->put("{$this->prefix}_breakpoints", json_encode($breakpoints), $this->ttl);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
