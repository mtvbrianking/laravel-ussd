<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Dom;

class OptionTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->store->get('_breakpoints'), true);

        $children = Dom::getElements($this->node->childNodes, null);

        $no_of_tags = \count($children);
        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);

        array_unshift($breakpoints, [$break => $this->incExp($pre)]);
        $this->store->put('_breakpoints', json_encode($breakpoints));

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', "{$exp}/*[1]");

        return '';
    }
}
