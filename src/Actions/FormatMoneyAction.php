<?php

namespace Bmatovu\Ussd\Actions;

use Illuminate\Contracts\Cache\Repository as CacheContract;

class FormatMoneyAction
{
    protected \DOMNode $node;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    protected float $amount;
    protected string $currency;

    public function __construct(\DOMNode $node, CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->node = $node;
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function handle(): ?string
    {
        $this->extractParameters($this->node);

        $formattedAmount = number_format($this->amount);

        $this->cache->put("{$this->prefix}_amount", "{$this->currency} {$formattedAmount}", $this->ttl);

        return '';
    }

    public function process(?string $answer): void
    {
    }

    protected function extractParameters(\DOMNode $node): void
    {
        $amount = $node->attributes->getNamedItem('amount')->nodeValue
            ?? $this->cache->get("{$this->prefix}_amount");

        if (! $amount) {
            throw new \Exception("Arg 'amount' is required.");
        }

        $this->amount = (float) $amount;

        $this->currency = $node->attributes->getNamedItem('currency')->nodeValue
            ?? $this->cache->get("{$this->prefix}_currency", 'UGX');
    }
}
