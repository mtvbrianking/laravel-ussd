<?php

namespace Bmatovu\Ussd\Dto;

// use Spatie\DataTransferObject\Caster;

class ItemArrayCaster // implements Caster
{
    public function cast(mixed $values): array
    {
        if (! \is_array($values)) {
            throw new \Exception('Can only cast arrays to Item');
        }

        return array_map(function ($value) {
            return new Item($value);
        }, $values);
    }
}
