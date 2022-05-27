<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

class ResponseTag extends BaseTag
{
    public function handle(): ?string
    {
        // $pre = $this->cache->get("{$this->prefix}_pre");
        // $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        // $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        $text = $this->node->attributes->getNamedItem('text')->nodeValue;

        // $output = $this->translate($text);

        throw new \Exception($text);
    }

    public function process(?string $answer): void
    {
    }
}
