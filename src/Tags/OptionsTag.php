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
            $body .= "\n{$pos}) " . $child->attributes->getNamedItem('text')->nodeValue;
        }

        if (!$this->node->attributes->getNamedItem('noback')) {
            $body .= "\n0) Back";
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        $header = $this->store->get('fails', 0)
            ? $this->readAttr('error', 'Invalid choice. Try again:')
            : $this->readAttr('header');

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Make a choice.');
        }

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $fails = (int) $this->store->get('fails') + 1;

        $this->store->put('fails', $fails);

        if ('0' === $answer) {
            if ($this->node->attributes->getNamedItem('noback')) {
                $this->retry($pre, $fails);
            }

            $exp = $this->goBack($pre, 2);

            $this->store->put('_exp', $exp);

            return;
        }

        $children = Dom::getElements($this->node->childNodes, 'option');

        if ((int) $answer > \count($children)) {
            $this->retry($pre, $fails);
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
            throw new \Exception('Invalid choice.');
        }

        // repeat step
        $this->store->put('_pre', $this->decExp($pre));
        $this->store->put('_exp', $pre);

        return;
    }
}
