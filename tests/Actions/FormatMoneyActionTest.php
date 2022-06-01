<?php

namespace Bmatovu\Ussd\Tests\Actions;

use Bmatovu\Ussd\Actions\BaseAction;
use Bmatovu\Ussd\Tests\TestCase;

class FormatMoneyActionTest extends TestCase
{
    public function testHandleAction()
    {
        $this->cache->put('prefix_exp', '/*[1]');

        $this->cache->put('prefix_amount', '12500');

        $node = $this->getNodeByTagName('<action name="format-money"/>', 'action');

        $tag = new FormatMoneyAction($node, $this->cache, 'prefix', 30);

        $output = $tag->handle();

        static::assertEmpty($output);
        static::assertSame('UGX 12,500', $this->cache->get('prefix_amount'));
        static::assertSame('/*[1]', $this->cache->get('prefix_pre'));
        static::assertSame('/*[2]', $this->cache->get('prefix_exp'));
    }

    public function testHandleActionMissingAttr()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage("Arg 'amount' is required.");

        $this->cache->put('prefix_exp', '/*[1]');

        // $this->cache->put('prefix_amount', '12500');

        $node = $this->getNodeByTagName('<action name="format-money"/>', 'action');

        $tag = new FormatMoneyAction($node, $this->cache, 'prefix', 30);

        $tag->handle();
    }
}

class FormatMoneyAction extends BaseAction
{
    public function handle(): ?string
    {
        $this->extractParameters($this->node);

        $formattedAmount = number_format($this->amount);

        $this->cache->put("{$this->prefix}_amount", "{$this->currency} {$formattedAmount}", $this->ttl);

        // $this->shiftCursor();
        return parent::handle();
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
