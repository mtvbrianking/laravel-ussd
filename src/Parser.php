<?php

namespace Bmatovu\Ussd;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Traits\ParserUtils;

class Parser
{
    use ParserUtils;

    protected \DOMXPath $xpath;
    protected string $sessionId;
    protected Store $store;

    public function __construct(\DOMXPath $xpath, string $expression, string $sessionId, string $serviceCode = '')
    {
        $this->xpath = $xpath;

        // $config = Container::getInstance()->make('config');
        $store = config('ussd.cache.store', 'file');
        $ttl = config('ussd.cache.ttl', 120);
        $this->store = new Store($store, $ttl, $sessionId);

        if ($this->sessionExists($sessionId)) {
            return;
        }

        $serviceCode = $this->clean($serviceCode);
        $this->store->put('_session_id', $sessionId);
        $this->store->put('_service_code', $serviceCode);
        $this->store->put('_answer', $serviceCode);
        $this->store->put('_pre', '');
        $this->store->put('_exp', $expression);
        $this->store->put('_breakpoints', '[]');
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $key => $value) {
            $this->store->put($key, $value);
        }

        return $this;
    }

    public function parse(?string $userInput = ''): string
    {
        // return $this->doParse($userInput);

        $answer = $this->getAnswer($userInput);

        \Illuminate\Support\Facades\Log::debug('__answers', [
            'old' => $this->store->get('_answer'),
            'input' => $userInput,
            'new' => $answer,
        ]);

        // return $output = $this->doParse($answer);

        $answers = explode('*', $answer);

        foreach ($answers as $answer) {
            $output = $this->doParse($answer);
        }

        return $output;
    }

    protected function doParse(?string $answer = ''): ?string
    {
        $this->doProcess($answer);

        $exp = $this->store->get('_exp');
        $node = $this->xpath->query($exp)->item(0);

        if (! $node) {
            $this->doBreak();
        }

        $output = $this->doRender();

        if (! $output) {
            return $this->doParse($answer);
        }

        return $output;
    }

    protected function doProcess(?string $answer): void
    {
        $pre = $this->store->get('_pre');

        if (! $pre) {
            return;
        }

        $preNode = $this->xpath->query($pre)->item(0);

        $tagName = $this->resolveTagName($preNode);
        $tag = $this->instantiateTag($tagName, [$preNode, $this->store]);

        if (! $tag instanceof AnswerableTag) {
            return;
        }

        $tag->process($answer);
    }

    protected function doBreak(): void
    {
        $exp = $this->store->get('_exp');

        $breakpoints = (array) json_decode((string) $this->store->get('_breakpoints'), true);

        if (! $breakpoints || ! isset($breakpoints[0][$exp])) {
            throw new \Exception('Missing tag');
        }

        $breakpoint = array_shift($breakpoints);
        $this->store->put('_exp', $breakpoint[$exp]);
        $this->store->put('_breakpoints', json_encode($breakpoints));
    }

    protected function doRender(): ?string
    {
        $exp = $this->store->get('_exp');

        $node = $this->xpath->query($exp)->item(0);

        $tagName = $this->resolveTagName($node);
        $tag = $this->instantiateTag($tagName, [$node, $this->store]);
        $output = $tag->handle();

        $exp = $this->store->get('_exp');
        $breakpoints = (array) json_decode((string) $this->store->get('_breakpoints'), true);

        if ($breakpoints && isset($breakpoints[0][$exp])) {
            $breakpoint = array_shift($breakpoints);
            $this->store->put('_exp', $breakpoint[$exp]);
            $this->store->put('_breakpoints', json_encode($breakpoints));
        }

        return $output;
    }
}
