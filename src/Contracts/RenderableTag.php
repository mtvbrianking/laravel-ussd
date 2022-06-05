<?php

namespace Bmatovu\Ussd\Contracts;

interface RenderableTag
{
    // public function render(): ?string;
    public function handle(): ?string;
}
