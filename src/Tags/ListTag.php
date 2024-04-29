<?php

namespace Bmatovu\Ussd\Tags;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Contracts\ListProvider;
use Bmatovu\Ussd\Support\Util;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ListTag extends BaseTag implements AnswerableTag
{
    public function handle(): ?string
    {
        $exp = $this->store->get('_exp');

        $providerName = Util::toPath($this->readAttr('provider'), 'Provider');

        $provider = $this->instantiateListProvider($providerName, [$this->store]);

        $list = $provider->load();

        $this->validate($list);

        $itemPrefix = $this->readAttr('prefix');
        $this->store->put("{$itemPrefix}_list", $list);

        $pos = 0;
        $body = '';
        foreach ($list as $item) {
            ++$pos;
            $body .= "\n{$pos}) " . $item['label'];
        }

        $this->store->put('_pre', $exp);
        $this->store->put('_exp', $this->incExp($exp));

        $header = $this->store->get('fails', 0)
            ? $this->readAttrText('error', 'InvalidChoice')
            : $this->readAttrText('header');

        return "{$header}{$body}";
    }

    public function process(?string $answer): void
    {
        $itemPrefix = $this->readAttr('prefix');
        $list = $this->store->pull("{$itemPrefix}_list");

        $item = $list[--$answer] ?? null;

        $pre = $this->store->get('_pre');

        if (!$item) {
            $fails = (int) $this->store->get('fails') + 1;

            $this->store->put('fails', $fails);

            if ($fails > $this->readAttr('retries', 1)) {
                throw new \Exception(trans('InvalidChoice'));
            }

            // repeat step
            $this->store->put('_pre', $this->decExp($pre));
            $this->store->put('_exp', $pre);

            return;
        }

        $this->store->put("{$itemPrefix}_id", $item['id']);
        $this->store->put("{$itemPrefix}_label", $item['label']);

        $this->store->put('_exp', $this->incExp($pre));
        $this->store->put('fails', 0);
    }

    protected function resolveProviderClass(string $providerName): string
    {
        $config = Container::getInstance()->make(ConfigRepository::class);

        $providerNs = $config->get('ussd.provider-ns', []);

        $fqcn = $providerName;

        foreach ($providerNs as $ns) {
            $fqcn = "{$ns}\\{$providerName}";
            Log::debug("1. fqcn --> {$fqcn}");
            if (Util::classExists($fqcn)) {
                return $fqcn;
            }
        }

        $this->store->put('missing_provider', $providerName);
        $this->store->put('missing_provider_fqcn', $fqcn);

        throw new \Exception(Util::hydrate($this->store, trans('MissingProvider')));
    }

    protected function instantiateListProvider(string $providerName, array $args = []): ListProvider
    {
        // $providerName = Str::studly("{$providerName}Provider");

        $fqcn = $this->resolveProviderClass($providerName);

        Log::debug("2. fqcn --> {$fqcn}");

        $provider = \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);

        if (!$provider instanceof ListProvider) {
            // change this to a translation...
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
