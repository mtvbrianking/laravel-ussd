<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\Expressions;
use Bmatovu\Ussd\Traits\Variables;

class BaseAction implements RenderableTag
{
    use Attributes, Expressions, Variables;

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
}
