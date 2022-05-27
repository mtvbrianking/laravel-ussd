<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;
use Illuminate\Support\Facades\Log;

class WhenTag implements Tag
{
    public function handle(): ?string
    {
        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->cache->get("{$this->prefix}_breakpoints"), true);

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $no_of_tags = $this->xpath->query('*', $node)->length;
        $children = Helper::getDomElements($this->node->childNodes, null);
        $no_of_tags = \count($children);

        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);

        array_unshift($breakpoints, [$break => $this->incExp($pre)]);
        $this->cache->put("{$this->prefix}_breakpoints", json_encode($breakpoints), $this->ttl);

        // Log::debug("BP       -->", ['break' => $break, 'resume' => $this->incExp($pre)]);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", "{$exp}/*[1]", $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[1]"]);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
