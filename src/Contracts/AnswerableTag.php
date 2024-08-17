<?php

namespace Bmatovu\Ussd\Contracts;

interface AnswerableTag
{
    /**
     * Process answer to the tag at the previous xpath.
     */
    public function process(?string $answer): void;
}
