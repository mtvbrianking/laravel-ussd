<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Exceptions\FlowBreakException;

class ResponseTag extends BaseTag
{
    public function handle(): ?string
    {
        $text = $this->readAttrText();

        throw new FlowBreakException($text);
    }
}
