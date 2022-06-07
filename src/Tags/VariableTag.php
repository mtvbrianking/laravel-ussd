<?php

namespace Bmatovu\Ussd\Tags;

class VariableTag extends BaseTag
{
    public function handle(): ?string
    {
        $name = $this->readAttr('name');
        $value = $this->readAttr('value');

        $this->store->put($name, $value);

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        return '';
    }
}
