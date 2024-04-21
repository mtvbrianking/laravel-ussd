<?php

namespace Bmatovu\Ussd\Actions;

use Bmatovu\Ussd\Contracts\AnswerableTag;

/**
 * Usage:
 *
 * ```xml
 * <action name="format-msisdn" msisdn="0743876123" country_code="256" />
 * <response text="{{_msisdn}}" /><!-- 256743876123 -->
 * ```
 */
class FormatMsisdnAction extends BaseAction implements AnswerableTag
{
    public function process(?string $answer): void
    {
        $msisdn = $this->readAttr('msisdn', $this->store->get('msisdn'));

        if (!$msisdn) {
            return;
        }

        $msisdn = preg_replace('/[^\d]/', '', $msisdn);

        $country_code = $this->readAttr('country_code', $this->store->get('country_code'));
        if ($country_code) {
            $msisdn = preg_replace('/^0/', $country_code, $msisdn);
        }

        $this->store->put('_msisdn', $msisdn);
    }
}
