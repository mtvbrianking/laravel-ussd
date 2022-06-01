<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Support\Helper;

class OptionsTag extends BaseTag
{
    public function handle(): ?string
    {
        $header = $this->node->attributes->getNamedItem('header')->nodeValue;

        $body = '';

        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        $children = Helper::getDomElements($this->node->childNodes, 'option');

        $pos = 0;
        foreach ($children as $child) {
            ++$pos;
            $body .= "\n{$pos}) ".$child->attributes->getNamedItem('text')->nodeValue;
        }

        if (! $this->node->attributes->getNamedItem('noback')) {
            $body .= "\n0) Back";
        }

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Make a choice.');
        }

        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        if ('0' === $answer) {
            if ($this->node->attributes->getNamedItem('noback')) {
                throw new \Exception('Invalid choice.');
            }

            $exp = $this->goBack($pre, 2);

            $this->cache->put("{$this->prefix}_exp", $exp, $this->ttl);

            return;
        }

        $children = Helper::getDomElements($this->node->childNodes, 'option');

        if ((int) $answer > \count($children)) {
            throw new \Exception('Invalid choice.');
        }

        $this->cache->put("{$this->prefix}_exp", "{$pre}/*[{$answer}]", $this->ttl);
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
