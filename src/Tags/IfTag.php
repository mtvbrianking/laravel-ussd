<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;

class IfTag extends BaseTag
{
    public function handle(): ?string
    {
        $key = $this->readAttr('key');
        $value = $this->readAttr('value');

        if ($this->fromCache($key) !== $value) {
            $exp = $this->fromCache('exp', $this->node->getNodePath());

            $this->toCache('pre', $exp);
            $this->toCache('exp', $this->incExp($exp));

            return '';
        }

        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->fromCache('breakpoints'), true);

        $this->toCache('pre', $exp);
        $this->toCache('exp', "{$exp}/*[1]");

        $children = Helper::getDomElements($this->node->childNodes, null);
        $no_of_tags = \count($children);

        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);
        array_unshift($breakpoints, [$break => $this->incExp($exp)]);
        $this->toCache('breakpoints', json_encode($breakpoints));

        return '';
    }
}
