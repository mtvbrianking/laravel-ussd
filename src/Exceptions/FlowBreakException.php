<?php

namespace Bmatovu\Ussd\Exceptions;

class FlowBreakException extends \RuntimeException
{
    public function __construct(string $message = 'Flow break', int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function make(string $message = 'Flow break', int $code = 0): self
    {
        return new static($message, $code);
    }
}
