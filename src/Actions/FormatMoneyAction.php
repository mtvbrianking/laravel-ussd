<?php

namespace Bmatovu\Ussd\Actions;

use Illuminate\Contracts\Cache\Repository as CacheContract;

class FormatMoneyAction
{
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;

    protected float $amount;
    protected string $currency;

    public function __construct(CacheContract $cache, string $prefix, ?int $ttl = null)
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    public function __invoke(\DOMNode $node): void
    {
        $this->extractParameters($node);

        $formattedAmount = number_format($this->amount);

        $this->cache->put("{$this->prefix}_amount", "{$this->currency} {$formattedAmount}", $this->ttl);
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
