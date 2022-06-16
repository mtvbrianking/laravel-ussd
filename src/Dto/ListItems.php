<?php

namespace Bmatovu\Ussd\Dto;

// use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\DataTransferObject;

class ListItems extends DataTransferObject
{
    // #[CastWith(ItemArrayCaster::class)]
    // public array $items;

    /** @var Item[] $items */
    public array $items;
}
