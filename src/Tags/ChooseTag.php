<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;
use Illuminate\Support\Facades\Log;

class ChooseTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $children = Helper::getDomElements($this->node->childNodes, null);
        // $no_of_tags = \count($children);

        // $whenEls = $this->xpath->query('when', $this->node);
        $whenEls = Helper::getDomElements($this->node->childNodes, 'when');

        // $whenEls = array_values($whenEls);

        $pos = 0;
        $isMatched = false;

        foreach ($whenEls as $whenEl) {
            ++$pos;
            $key = $whenEl->attributes->getNamedItem('key')->nodeValue;
            $val = $whenEl->attributes->getNamedItem('value')->nodeValue;

            $var = $this->fromCache($key);;

            if ($var !== $val) {
                continue;
            }

            $isMatched = true;

            $this->toCache('pre', $exp);
            $this->toCache('exp', "{$exp}/*[{$pos}]");

            break;
        }

        if ($isMatched) {
            return '';
        }

        $this->toCache('pre', $exp);

        $otherwiseEls = Helper::getDomElements($this->node->childNodes, 'otherwise');
        // $otherwiseEl = $this->xpath->query('otherwise', $this->node)->item(0);

        if (! isset($otherwiseEls[0])) {
            $this->toCache('exp', $this->incExp($exp));

            return '';
        }

        ++$pos;

        $this->toCache('exp', "{$exp}/*[{$pos}]");

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[{$pos}]"]);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
