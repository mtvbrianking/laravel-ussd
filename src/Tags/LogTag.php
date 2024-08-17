<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

/**
 * ```xml
 * <log message="{{alias}}" context="{{fname}},{{lname}}" />
 * ```.
 */
class LogTag extends BaseTag
{
    public function handle(): ?string
    {
        $level = $this->readAttr('level', 'debug');
        $message = $this->readAttrText('message', '');
        $context = $this->readAttr('context', '');

        Log::{$level}($message, array_filter(explode(',', $context)));

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        return '';
    }
}
