<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;
use Illuminate\Support\Facades\Log;

class ChooseTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $children = Helper::getDomElements($this->node->childNodes, null);
        // $no_of_tags = \count($children);

        // $whenEls = $this->xpath->query('when', $this->node);
        $whenEls = Helper::getDomElements($this->node->childNodes, 'when');

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

        $otherwiseEls = Helper::getDomElements($this->node->childNodes, 'otherwise');
        // $otherwiseEl = $this->xpath->query('otherwise', $this->node)->item(0);

        if (! isset($otherwiseEls[0])) {
            return '';
        }

        ++$pos;

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", "{$exp}/*[{$pos}]", $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[{$pos}]"]);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
