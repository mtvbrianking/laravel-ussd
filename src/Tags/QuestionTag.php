<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Support\Util;

class QuestionTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $current = $this->store->get('_exp');

        // shift cursor...
        $this->store->put('_pre', $current);
        $this->store->put('_exp', $this->incExp($current));

        $fails = $this->store->get('fails', 0);

        return $fails
            ? $this->readAttrText('error', 'ValidationErrorRetry')
            : $this->readAttrText();
    }

    public function process(?string $answer): void
    {
        $current = $this->store->get('_pre');
        $next = $this->store->get('_exp');

        $fails = (int) $this->store->get('fails') + 1;

        $this->store->put('fails', $fails);

        if ($pattern = $this->readAttr('pattern')) {
            $matched = preg_match(Util::regex($pattern), $answer);

            if ($matched === false) {
                throw new \Exception('Validation exception');
            }

            if ($matched === 0) {
                if ($fails > $this->readAttr('retries', 1)) {
                    throw new \Exception($this->readAttrText('error', 'ValidationError'));
                }

                // repeat step
                $this->store->put('_pre', $this->decExp($current));
                $this->store->put('_exp', $current);

                return;
            }
        }

        $this->store->put($this->readAttr('name'), $answer);

        // reset cursor...
        $this->store->put('_pre', $current);
        $this->store->put('_exp', $next);

        // reset failures counter...
        $this->store->put('fails', 0);
    }
}
