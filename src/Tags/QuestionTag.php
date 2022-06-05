<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;

class QuestionTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));

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
