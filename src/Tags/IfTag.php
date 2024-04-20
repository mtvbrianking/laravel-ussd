<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Dom;
use Bmatovu\Ussd\Support\Util;

class IfTag extends BaseTag
{
    public function handle(): ?string
    {
        $key = $this->readAttr('key');
        $var = $this->store->get($key);
        $cond = $this->readAttr('cond', 'eq');
        $value = $this->readAttr('value');

        if (!$var) {
            trigger_error("Undefined variable \${$key}.", E_USER_WARNING);
        }

        if (!Util::compare($var, $cond, $value)) {
            $exp = $this->store->get('_exp', $this->node->getNodePath());

            $this->store->put('_pre', $exp);
            $this->store->put('_exp', $this->incExp($exp));

            return '';
        }

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->store->get('_breakpoints'), true);

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', "{$exp}/*[1]");

        $children = Dom::getElements($this->node->childNodes, null);
        $no_of_tags = \count($children);

        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);
        array_unshift($breakpoints, [$break => $this->incExp($exp)]);
        $this->store->put('_breakpoints', json_encode($breakpoints));

        return '';
    }
}
