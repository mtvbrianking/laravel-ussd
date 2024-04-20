<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Dom;
use Bmatovu\Ussd\Support\Util;

class ChooseTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $whenEls = Dom::getElements($this->node->childNodes, 'when');

        $pos = 0;
        $isMatched = false;

        foreach ($whenEls as $whenEl) {
            ++$pos;
            $key = $whenEl->attributes->getNamedItem('key')->nodeValue;
            $cond = $whenEl->attributes->getNamedItem('cond')->nodeValue ?? 'eq';
            $val = $whenEl->attributes->getNamedItem('value')->nodeValue;

            $var = $this->store->get($key);

            if (!Util::compare($var, $cond, $val)) {
                continue;
            }

            $isMatched = true;

            $this->store->put('_pre', $exp);
            $this->store->put('_exp', "{$exp}/*[{$pos}]");

            break;
        }

        if ($isMatched) {
            return '';
        }

        $this->store->put('_pre', $exp);

        $otherwiseEls = Dom::getElements($this->node->childNodes, 'otherwise');

        if (!isset($otherwiseEls[0])) {
            $this->store->put('_exp', $this->incExp($exp));

            return '';
        }

        ++$pos;

        $this->store->put('_exp', "{$exp}/*[{$pos}]");

        return '';
    }
}
