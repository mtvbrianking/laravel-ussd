<?php

namespace Bmatovu\Ussd\Dto;

use Spatie\DataTransferObject\DataTransferObject;

// #[Strict]
class Item extends DataTransferObject
{
    public string|int $id;
    public string $label;
}
