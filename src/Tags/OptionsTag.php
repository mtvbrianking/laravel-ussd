<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\Tag;
use Bmatovu\Ussd\Support\Helper;
use Bmatovu\Ussd\Traits\Expressions;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

class OptionsTag implements Tag
{
    use Expressions;

    protected \DOMXPath $xpath;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    public function __construct(\DOMXPath $xpath, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->xpath = $xpath;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(\DOMNode $node): ?string
    {
        $header = $node->attributes->getNamedItem('header')->nodeValue;

        $body = '';

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp");

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        // $children = $this->xpath->query('option', $node);

        // foreach ($children as $idx => $child) {
        //     $pos = $idx + 1;
        //     $body .= "\n{$pos}) " . $child->attributes->getNamedItem("text")->nodeValue;
        // }

        $children = Helper::getDomElements($node->childNodes, 'option');

        $pos = 0;
        foreach ($children as $child) {
            ++$pos;
            $body .= "\n{$pos}) ".$child->attributes->getNamedItem('text')->nodeValue;
        }

        if (! $node->attributes->getNamedItem('noback')) {
            $body .= "\n0) Back";
        }

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);
        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        return "{$header}{$body}";
    }

    public function process(\DOMNode $node, ?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Invalid answer.');
        }

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp");

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        if (0 === $answer) {
            if ($node->attributes->getNamedItem('noback')) {
                throw new \Exception('Invalid option.');
            }

            $exp = $this->goBack($pre, 2);

            // Log::debug("GoBack   -->", ['exp' => $exp]);

            $this->cache->put("{$this->prefix}_exp", $exp, $this->ttl);

            return;
        }

        // if((int) $answer > $this->xpath->query('option', $node)->length) {}

        $children = Helper::getDomElements($node->childNodes, 'option');

        if ((int) $answer > \count($children)) {
            throw new \Exception('Invalid option.');
        }

        $this->cache->put("{$this->prefix}_exp", "{$pre}/*[{$answer}]", $this->ttl);
        // Log::debug("CheckOut -->", ['pre' => $pre, 'exp' => "{$pre}/*[{$answer}]"]);
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
