<?php

namespace Bmatovu\Ussd\Actions;

/**
 * Usage:
 *
 * ```xml
 * <action name="format_msisdn" msisdn="0743876123" country_code="256" />
 * <response text="{{fmt_msisdn}}" /><!-- 256743876123 -->
 * ```
 */
class FormatMsisdnAction extends BaseAction
{
    public function process(?string $answer): void
    {
        $msisdn = $this->readAttr('msisdn', $this->store->get('msisdn'));

        if (!$msisdn) {
            return;
        }

        $msisdn = preg_replace('/[^\d]/', '', $msisdn);

        $country_code = $this->readAttr('country_code');
        if ($country_code) {
            $msisdn = preg_replace('/^0/', $country_code, $msisdn);
        }

        $prefix = $this->readAttr('prefix', 'fmt');

        $this->store->put("{$prefix}_msisdn", $msisdn);
    }
}
