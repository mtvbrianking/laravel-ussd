<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;

class QuestionTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        return $this->readAttr('text');
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Question requires an answer.');
        }

        $name = $this->readAttr('name');

        $this->store->put($name, $answer);
    }
}
