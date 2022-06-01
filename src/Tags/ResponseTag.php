<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

class ResponseTag extends BaseTag
{
    public function handle(): ?string
    {
        // $pre = $this->fromCache('pre');
        // $exp = $this->fromCache('exp', $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $this->toCache('pre', $exp);
        // $this->toCache('exp', $this->incExp($exp));

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        $text = $this->readAttr('text');

        // $output = $this->translate($text);

        throw new \Exception($text);
    }

    public function process(?string $answer): void
    {
    }
}
