<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Contracts\ListProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ListTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $header = $this->readAttr('header');

        $body = '';

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $provider = $this->instantiateListProvider($this->readAttr('provider'), [$this->store]);
        $list = $provider->load();

        $this->validate($list);

        $itemPrefix = $this->readAttr('prefix');
        $this->store->put("{$itemPrefix}_list", $list);

        $pos = 0;
        foreach ($list as $item) {
            ++$pos;
            $body .= "\n{$pos}) ".$item['label'];
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        if ('' === $answer) {
            throw new \Exception('Make a choice.');
        }

        $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $itemPrefix = $this->readAttr('prefix');
        $list = $this->store->pull("{$itemPrefix}_list");

        $item = $list[--$answer] ?? null;

        if (! $item) {
            throw new \Exception('Invalid choice.');
        }

        $this->store->put("{$itemPrefix}_id", $item['id']);
        $this->store->put("{$itemPrefix}_label", $item['label']);
    }

    protected function resolveProviderClass(string $providerName): string
    {
        $config = Container::getInstance()->make(ConfigRepository::class);

        $providerNs = $config->get('ussd.provider-ns', []);

        $fqcn = $providerName;

        foreach ($providerNs as $ns) {
            $fqcn = "{$ns}\\{$providerName}";
            Log::debug("{$providerName} --> {$fqcn}");
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        throw new \Exception("Missing provider: {$providerName}.\nClass: {$fqcn}.");
    }

    protected function instantiateListProvider(string $providerName, array $args = []): ListProvider
    {
        $providerName = Str::studly("{$providerName}Provider");

        $fqcn = $this->resolveProviderClass($providerName);

        $provider = \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);

        if (! $provider instanceof ListProvider) {
            throw new \Exception("'{$providerName}' must implement the 'ListProvider' interface.");
        }

        return $provider;
    }

    protected function validate(array $list): void
    {
        $validator = Validator::make($list, [
            '*.id' => 'required',
            '*.label' => 'required',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->toJson());
        }
    }
}
