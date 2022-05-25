<?php

namespace Bmatovu\Ussd\Contracts;

interface Tag
{
    public function handle(): ?string;

    public function process(?string $answer): void;
}
