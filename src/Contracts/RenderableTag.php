<?php

namespace Bmatovu\Ussd\Contracts;

interface RenderableTag
{
    /**
     * Render tag at the current xpath
     */
    public function handle(): ?string;
}
