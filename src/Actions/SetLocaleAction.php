<?php

namespace Bmatovu\Ussd\Actions;

use Illuminate\Support\Facades\App;

/**
 * Usage:
 *
 * ```xml
 * <action name="set_locale" />
 * <response text="{{locale}}" /><!-- en -->
 * ```
 */
class SetLocaleAction extends BaseAction
{
    public function process(?string $answer): void
    {
        $locale = $this->readAttr('locale', $this->store->get('locale', 'en'));

        App::setLocale($locale);

        $this->store->put('locale', $locale);
    }
}
