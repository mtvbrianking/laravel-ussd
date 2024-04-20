<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\Expressions;

class BaseTag implements RenderableTag
{
    use Attributes;
    use Expressions;

    protected \DOMNode $node;
    protected Store $store;

    protected ?string $pre;
    protected string $exp;

    public function __construct(\DOMNode $node, Store $store)
    {
        $this->node = $node;
        $this->store = $store;

        $this->pre = $this->store->get('_pre');
        $this->exp = $this->store->get('_exp', $this->node->getNodePath());
    }

    public function handle(): ?string
    {
        return '';
    }
}
