<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Support\Dom;

class OptionsTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $body = '';

        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $children = Dom::getElements($this->node->childNodes, 'option');

        $pos = 0;
        foreach ($children as $child) {
            ++$pos;
            $body .= "\n{$pos}) " . $this->readAttrText('text', '', $child);
        }

        if (!$this->readAttrText('noback')) {
            $body .= "\n0) " . trans('Back');
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        $header = $this->store->get('fails', 0)
            ? $this->readAttrText('error', 'InvalidChoice')
            : $this->readAttrText('header');

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception(trans('Make a choice.'));
        }

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $fails = (int) $this->store->get('fails') + 1;

        $this->store->put('fails', $fails);

        if ('0' === $answer) {
            if ($this->readAttr('noback')) {
                $this->retry($pre, $fails);

                return;
            }

            $exp = $this->goBack($pre, 2);

            $this->store->put('_exp', $exp);
            $this->store->put('fails', 0);

            return;
        }

        $children = Dom::getElements($this->node->childNodes, 'option');

        if ((int) $answer > \count($children)) {
            $this->retry($pre, $fails);

            return;
        }

        $this->store->put('_exp', "{$pre}/*[{$answer}]");
        $this->store->put('fails', 0);
    }

    protected function goBack(string $exp, int $steps = 1): string
    {
        $count = 0;

        $exp = preg_replace_callback("|(\\/\\*\\[\\d\\]){{$steps}}$|", function ($matches) {
            return '';
        }, $exp, 1, $count);

        return 1 === $count ? $exp : '';
    }

    /**
     * Retry step
     *
     * @param string $pre
     * @param int $fails
     *
     * @return void
     */
    protected function retry($pre, $fails)
    {
        if ($fails > $this->readAttr('retries', 1)) {
            throw new \Exception(trans('InvalidChoice'));
        }

        // repeat step
        $this->store->put('_pre', $this->decExp($pre));
        $this->store->put('_exp', $pre);

        return;
    }
}
