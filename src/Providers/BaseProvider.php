<?php

namespace Bmatovu\Ussd\Providers;

use Bmatovu\Ussd\Contracts\ListProvider;
use Bmatovu\Ussd\Store;
use Bmatovu\Ussd\Traits\Attributes;
use Bmatovu\Ussd\Traits\Variables;

abstract class BaseProvider implements ListProvider
{
    use Attributes;
    use Variables;

    protected Store $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    // abstract public function load(): array;
}
