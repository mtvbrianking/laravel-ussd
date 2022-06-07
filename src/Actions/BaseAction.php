<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\Expressions;

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

        return '';
    }

    protected function shiftCursor(): void
    {
        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));
    }
}
