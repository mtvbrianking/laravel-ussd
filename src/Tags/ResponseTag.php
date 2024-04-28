<?php

namespace Bmatovu\Ussd\Tags;

class ResponseTag extends BaseTag
{
    public function handle(): ?string
    {
        $text = $this->readAttrText();

        throw new \Exception($text);
    }
}
