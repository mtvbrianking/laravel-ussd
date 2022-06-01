<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;
use Illuminate\Support\Facades\Log;

class OtherwiseTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());
        $breakpoints = (array) json_decode((string) $this->fromCache('breakpoints'), true);

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $no_of_tags = $this->xpath->query('*', $node)->length;
        $children = Helper::getDomElements($this->node->childNodes, null);
        $no_of_tags = \count($children);

        $break = $this->incExp("{$exp}/*[1]", $no_of_tags);

        array_unshift($breakpoints, [$break => $this->incExp($pre)]);
        $this->toCache('breakpoints', json_encode($breakpoints));

        // Log::debug("BP       -->", ['break' => $break, 'resume' => $this->incExp($pre)]);

        $this->toCache('pre', $exp);
        $this->toCache('exp', "{$exp}/*[1]");

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => "{$exp}/*[1]"]);

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
