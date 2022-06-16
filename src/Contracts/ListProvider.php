<?php

namespace Bmatovu\Ussd\Contracts;

interface ListProvider
{
    public function load(): array;
}
