<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

class QuestionTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        return $this->readAttr('text');
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Question requires an answer.');
        }

        $name = $this->readAttr('name');

        $this->cache->put("{$this->prefix}_{$name}", $answer, $this->ttl);
    }
}
