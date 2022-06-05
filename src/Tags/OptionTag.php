<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;

class OptionTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->fromCache('breakpoints'), true);

        $children = Helper::getDomElements($this->node->childNodes, null);

        $no_of_tags = \count($children);
        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);

        array_unshift($breakpoints, [$break => $this->incExp($pre)]);
        $this->toCache('breakpoints', json_encode($breakpoints));

        $this->toCache('pre', $exp);
        $this->toCache('exp', "{$exp}/*[1]");

        return '';
    }
}
