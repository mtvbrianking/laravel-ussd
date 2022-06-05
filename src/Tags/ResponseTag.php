<?php

namespace Bmatovu\Ussd\Tags;

class ResponseTag extends BaseTag
{
    public function handle(): ?string
    {
        $text = $this->readAttr('text');

        throw new \Exception($text);
    }
}
