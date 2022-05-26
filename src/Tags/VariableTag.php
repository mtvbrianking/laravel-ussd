<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

class VariableTag extends BaseTag
{
    public function handle(): ?string
    {
        $name = $this->node->attributes->getNamedItem('name')->nodeValue;
        $value = $this->node->attributes->getNamedItem('value')->nodeValue;

        $this->cache->put("{$this->prefix}_{$name}", $value, $this->ttl);

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
