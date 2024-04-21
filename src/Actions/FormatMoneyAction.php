<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\AnswerableTag;

/**
 * Usage:
 *
 * ```xml
 * <action name="format-money" amount="15600.5075" currency="USD" decimals="2" />
 * <response text="{{_amount}}" /><!-- USD 15,600.50 -->
 * ```
 */
class FormatMoneyAction extends BaseAction implements AnswerableTag
{
    public function process(?string $answer): void
    {
        $amount = $this->readAttr('amount', $this->store->get('amount'));

        if (!$amount) {
            return;
        }

        $money = number_format(
            (float) $amount,
            (int) $this->readAttr('decimals', $this->store->get('decimals', 0)),
            $this->readAttr('decimal_separator', $this->store->get('decimal_separator', '.')),
            $this->readAttr('thousands_separator', $this->store->get('thousands_separator', ','))
        );

        $currency = $this->readAttr('currency', $this->store->get('currency'));

        $this->store->put('_amount', "{$currency} {$money}");
    }
}
