<?php

namespace Bmatovu\Ussd\Traits;

use Bmatovu\Ussd\Support\Dom;

trait Variables
{
    protected function getVar(string $name, string $default = '', string $nodeName = 'variable'): string
    {
        $children = Dom::getElements($this->node->childNodes, $nodeName);

        foreach ($children as $child) {
            if ($name === $this->readAttrText('name', '', $child)) {
                return $this->readAttrText('value', $default, $child);
            }
        }

        return $default;
    }

    protected function getVars(string $nodeName = 'variable'): array
    {
        $children = Dom::getElements($this->node->childNodes, $nodeName);

        $variables = [];
        foreach ($children as $child) {
            $name = $this->readAttrText('name', '', $child);
            $value = $this->readAttrText('value', '', $child);

            $variables[$name] = $value;
        }

        return $variables;
    }
}
