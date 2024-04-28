<?php

namespace Bmatovu\Ussd\Traits;

use Bmatovu\Ussd\Support\Util;
use DOMNode;

trait Attributes
{
    public function readAttr(string $name, $default = '', DOMNode $node = null)
    {
        if (!$node) {
            return $this->node->attributes->getNamedItem($name)->nodeValue ?? $default;
        }

        return $node->attributes->getNamedItem($name)->nodeValue ?? $default;
    }

    public function readAttrText(string $name = 'text', $default = '', DOMNode $node = null)
    {
        $value = $this->readAttr($name, $default, $node);

        if (!$value) {
            return $value;
        }

        return Util::hydrate($this->store, trans($value));
    }
}
