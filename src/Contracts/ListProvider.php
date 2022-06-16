<?php

namespace Bmatovu\Ussd\Contracts;

use Bmatovu\Ussd\Dto\ListItems;

interface ListProvider
{
    public function load(): ListItems;
}
