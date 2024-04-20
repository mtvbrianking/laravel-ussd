<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Contracts\ListProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ListTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        // $header = $this->readAttr('header');

        $body = '';

        // $pre = $this->store->get('_pre');
        $exp = $this->store->get('_exp', $this->node->getNodePath());

        $provider = $this->instantiateListProvider($this->readAttr('provider'), [$this->store]);
        $list = $provider->load();

        $this->validate($list);

        $itemPrefix = $this->readAttr('prefix');
        $this->store->put("{$itemPrefix}_list", $list);

        $pos = 0;
        foreach ($list as $item) {
            ++$pos;
            $body .= "\n{$pos}) " . $item['label'];
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        $header = $this->store->get('fails', 0)
            ? $this->readAttr('error', 'Invalid choice. Try again:')
            : $this->readAttr('header');

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        $itemPrefix = $this->readAttr('prefix');
        $list = $this->store->pull("{$itemPrefix}_list");

        $item = $list[--$answer] ?? null;

        if (!$item) {
            $fails = (int) $this->store->get('fails') + 1;

            $this->store->put('fails', $fails);

            if ($fails > $this->readAttr('retries', 1)) {
                throw new \Exception('Invalid choice.');
            }

            $pre = $this->store->get('_pre');

            // repeat step
            $this->store->put('_pre', $this->decExp($pre));
            $this->store->put('_exp', $pre);

            return;
        }

        $this->store->put("{$itemPrefix}_id", $item['id']);
        $this->store->put("{$itemPrefix}_label", $item['label']);
        $this->store->put('fails', 0);
    }

    protected function resolveProviderClass(string $providerName): string
    {
        $config = Container::getInstance()->make(ConfigRepository::class);

        $providerNs = $config->get('ussd.provider-ns', []);

        $fqcn = $providerName;

        foreach ($providerNs as $ns) {
            $fqcn = "{$ns}\\{$providerName}";
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

        if (!$provider instanceof ListProvider) {
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
