<?php

namespace Bmatovu\Ussd\Contracts;

interface AnswerableTag
{
    // public function reply(?string $answer): void;
    public function process(?string $answer): void;
}
