<?php

namespace Bmatovu\Ussd\Contracts;

interface Tag
{
    public function handle(\DOMNode $node): ?string;

    public function process(\DOMNode $node, ?string $answer): void;
}
