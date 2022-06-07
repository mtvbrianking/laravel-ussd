<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Support\Dom;

class OptionsTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $header = $this->node->attributes->getNamedItem('header')->nodeValue;

        $body = '';

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $children = Dom::getElements($this->node->childNodes, 'option');

        $pos = 0;
        foreach ($children as $child) {
            ++$pos;
            $body .= "\n{$pos}) ".$child->attributes->getNamedItem('text')->nodeValue;
        }

        if (! $this->node->attributes->getNamedItem('noback')) {
            $body .= "\n0) Back";
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Make a choice.');
        }

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        if ('0' === $answer) {
            if ($this->node->attributes->getNamedItem('noback')) {
                throw new \Exception('Invalid choice.');
            }

            $exp = $this->goBack($pre, 2);

            $this->store->put('_exp', $exp);

            return;
        }

        $children = Dom::getElements($this->node->childNodes, 'option');

        if ((int) $answer > \count($children)) {
            throw new \Exception('Invalid choice.');
        }

        $this->store->put('_exp', "{$pre}/*[{$answer}]");
    }

    protected function goBack(string $exp, int $steps = 1): string
    {
        $count = 0;

        $exp = preg_replace_callback("|(\\/\\*\\[\\d\\]){{$steps}}$|", function ($matches) {
            return '';
        }, $exp, 1, $count);

        return 1 === $count ? $exp : '';
    }
}
