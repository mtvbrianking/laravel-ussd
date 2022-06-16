<?php

namespace Bmatovu\Ussd\Dto;

use Spatie\DataTransferObject\DataTransferObject;

// #[Strict]
class Item extends DataTransferObject
{
    /** @var int|string $id */
    public $id;
    public string $label;
}
