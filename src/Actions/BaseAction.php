<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\Expressions;
use Bmatovu\Ussd\Support\Dom;

class BaseAction implements RenderableTag
{
    use Attributes;
    use Expressions;

    protected \DOMNode $node;
    protected Store $store;

    public function __construct(\DOMNode $node, Store $store)
    {
        $this->node = $node;
        $this->store = $store;
    }

    public function handle(): ?string
    {
        $this->shiftCursor();

        $fails = $this->store->get('fails', 0);

        return $fails
            ? $this->readAttrText('error', 'InternalError')
            : $this->readAttrText();
    }

    protected function shiftCursor(): void
    {
        $exp = $this->store->get('_exp');

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));
    }

    protected function getVar(string $name, string $default = '', string $nodeName = 'variable'): string
    {
        $children = Dom::getElements($this->node->childNodes, $nodeName);

        foreach ($children as $child) {
            if ($name == $this->readAttrText('name', '', $child))
                return $this->readAttrText('value', $default, $child);
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
