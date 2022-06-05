<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;

class ChooseTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        $whenEls = Helper::getDomElements($this->node->childNodes, 'when');

        $pos = 0;
        $isMatched = false;

        foreach ($whenEls as $whenEl) {
            ++$pos;
            $key = $whenEl->attributes->getNamedItem('key')->nodeValue;
            $val = $whenEl->attributes->getNamedItem('value')->nodeValue;

            $var = $this->fromCache($key);

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

        if (! isset($otherwiseEls[0])) {
            $this->toCache('exp', $this->incExp($exp));

            return '';
        }

        ++$pos;

        $this->toCache('exp', "{$exp}/*[{$pos}]");

        return '';
    }
}
