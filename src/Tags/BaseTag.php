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

    public function __construct(\DOMNode $node, Store $store)
    {
        $this->node = $node;
        $this->store = $store;
    }

    public function handle(): ?string
    {
        return '';
    }
}
