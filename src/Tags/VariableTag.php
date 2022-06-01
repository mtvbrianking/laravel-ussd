<?php

namespace Bmatovu\Ussd\Tags;

class VariableTag extends BaseTag
{
    public function handle(): ?string
    {
        $name = $this->readAttr('name');
        $value = $this->readAttr('value');

        $this->toCache($name, $value);

        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));

        return '';
    }

    public function process(?string $answer): void
    {
    }
}
