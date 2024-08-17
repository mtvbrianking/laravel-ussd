<?php

namespace Bmatovu\Ussd\Tests\Actions;

use Bmatovu\Ussd\Actions\BaseAction;
use Bmatovu\Ussd\Tests\TestCase;

class FormatMoneyActionTest extends TestCase
{
    public function testHandleAction()
    {
        $this->store->put('_exp', '/*[1]');

        $this->store->put('amount', '12500');

        $node = $this->getNodeByTagName('<action name="format-money"/>', 'action');

        $tag = new FormatMoneyAction($node, $this->store);

        $output = $tag->handle();

        self::assertEmpty($output);
        self::assertSame('UGX 12,500', $this->store->get('amount'));
        self::assertSame('/*[1]', $this->store->get('_pre'));
        self::assertSame('/*[2]', $this->store->get('_exp'));
    }

    public function testHandleActionMissingAttr()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage("Arg 'amount' is required.");

        $this->store->put('_exp', '/*[1]');

        // $this->store->put('amount', '12500');

        $node = $this->getNodeByTagName('<action name="format-money"/>', 'action');

        $tag = new FormatMoneyAction($node, $this->store);

        $tag->handle();
    }
}

class FormatMoneyAction extends BaseAction
{
    public function handle(): ?string
    {
        $this->extractParameters($this->node);

        $formattedAmount = number_format($this->amount);

        $this->store->put('amount', "{$this->currency} {$formattedAmount}");

        // $this->shiftCursor();
        return parent::handle();
    }

    public function process(?string $answer): void {}

    protected function extractParameters(\DOMNode $node): void
    {
        $amount = $node->attributes->getNamedItem('amount')->nodeValue
            ?? $this->store->get('amount');

        if (! $amount) {
            throw new \Exception("Arg 'amount' is required.");
        }

        $this->amount = (float) $amount;

        $this->currency = $node->attributes->getNamedItem('currency')->nodeValue
            ?? $this->store->get('currency', 'UGX');
    }
}
