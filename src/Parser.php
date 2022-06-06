<?php

namespace Bmatovu\Ussd;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Contracts\RenderableTag;
use Bmatovu\Ussd\Support\Arr;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Parser
{
    protected \DOMXPath $xpath;
    protected CacheContract $cache;
    protected string $prefix;
    protected int $ttl;
    // protected array $answers = [''];

    public function __construct(\DOMXPath $xpath, array $options, CacheContract $cache, ?int $ttl = null)
    {
        $this->xpath = $xpath;
        $this->cache = $cache;
        $this->ttl = $ttl;

        $this->bootstrap($options);
    }

    public function parse(?string $userInput): string
    {
        // $preAnswer = $this->cache->get("{$this->prefix}_answer");

        // $answer = $this->clean($userInput);

        // if ($preAnswer) {
        //     $answer = $this->clean(str_replace($preAnswer, '', $userInput));
        // }

        // $this->answers = explode('*', $answer);

        // $this->cache->put("{$this->prefix}_answer", $userInput, $this->ttl);

        // .......................

        $this->processResponse($userInput);

        $exp = $this->cache->get("{$this->prefix}_exp");

        $node = $this->xpath->query($exp)->item(0);

        if (! $node) {
            $this->setBreakpoint();
        }

        $output = $this->renderNext();

        if (! $output) {
            return $this->parse($userInput);
        }

        // $answer = array_shift($this->answers);
        // if($answer) {
        //     return $this->parse($answer);
        // }

        return $output;
    }

    protected function clean(string $code = ''): string
    {
        if (! $code) {
            return $code;
        }

        return rtrim(ltrim($code, '*'), '#');
    }

    protected function sessionExists(string $sessionId): bool
    {
        $preSessionId = $this->cache->get("{$this->prefix}_session_id", '');

        return $preSessionId === $sessionId;
    }

    protected function bootstrap(array $options)
    {
        $required = ['session_id', 'phone_number', 'service_code', 'expression'];

        if ($missing = Arr::keysDiff($required, $options)) {
            $msg = array_pop($missing);

            if ($missing) {
                $msg = implode(', ', $missing).', and '.$msg;
            }

            throw new \Exception('Missing parser options: '.$msg);
        }

        [
            'session_id' => $session_id,
            'phone_number' => $phone_number,
            'service_code' => $service_code,
            'expression' => $expression,
        ] = $options;

        $this->prefix = "{$phone_number}{$service_code}";

        // ...

        $preSessionId = $this->cache->get("{$this->prefix}_session_id", '');

        if ($this->sessionExists($session_id)) {
            return;
        }

        $this->cache->put("{$this->prefix}_session_id", $session_id, $this->ttl);
        $this->cache->put("{$this->prefix}_service_code", $this->clean($service_code), $this->ttl);
        $this->cache->put("{$this->prefix}_answer", $this->clean($service_code), $this->ttl);
        $this->cache->put("{$this->prefix}_phone_number", $phone_number, $this->ttl);

        $this->cache->put("{$this->prefix}_pre", '', $this->ttl);
        $this->cache->put("{$this->prefix}_exp", $expression, $this->ttl);
        $this->cache->put("{$this->prefix}_breakpoints", '[]', $this->ttl);
    }

    protected function processResponse(?string $userInput): void
    {
        $pre = $this->cache->get("{$this->prefix}_pre");

        if (! $pre) {
            return;
        }

        $preNode = $this->xpath->query($pre)->item(0);

        $tagName = $this->resolveTagName($preNode);
        $tag = $this->createTag($tagName, [$preNode, $this->cache, $this->prefix, $this->ttl]);

        if (! $tag instanceof AnswerableTag) {
            return;
        }

        $answer = $this->determineAnswer($userInput);

        // $answer = array_shift($this->answers);
        // $this->cache->put("{$this->prefix}_answer", "{$userInput}*{$answer}", $this->ttl);

        $tag->process($answer);
    }

    protected function determineAnswer(?string $userInput): ?string
    {
        // $serviceCode = $this->cache->get("{$this->prefix}_service_code");
        $preAnswer = $this->cache->get("{$this->prefix}_answer");

        if (! $preAnswer) {
            return $userInput;
        }

        $answer = $this->clean(str_replace($preAnswer, '', $userInput));

        $this->cache->put("{$this->prefix}_answer", $userInput, $this->ttl);

        // Log::debug('answers ---', [
        //     'pre_answer' => $preAnswer,
        //     'input' => $userInput,
        //     'answer' => $answer,
        // ]);

        return $answer;
    }

    protected function setBreakpoint(): void
    {
        $exp = $this->cache->get("{$this->prefix}_exp");

        $breakpoints = (array) json_decode((string) $this->cache->get("{$this->prefix}_breakpoints"), true);

        if (! $breakpoints || ! isset($breakpoints[0][$exp])) {
            throw new \Exception('Missing tag');
        }

        $breakpoint = array_shift($breakpoints);
        $this->cache->put("{$this->prefix}_exp", $breakpoint[$exp], $this->ttl);
        $this->cache->put("{$this->prefix}_breakpoints", json_encode($breakpoints), $this->ttl);
    }

    protected function renderNext(): ?string
    {
        $exp = $this->cache->get("{$this->prefix}_exp");

        $node = $this->xpath->query($exp)->item(0);

        $tagName = $this->resolveTagName($node);
        $tag = $this->createTag($tagName, [$node, $this->cache, $this->prefix, $this->ttl]);
        $output = $tag->handle();

        $exp = $this->cache->get("{$this->prefix}_exp");
        $breakpoints = (array) json_decode((string) $this->cache->get("{$this->prefix}_breakpoints"), true);

        if ($breakpoints && isset($breakpoints[0][$exp])) {
            $breakpoint = array_shift($breakpoints);
            $this->cache->put("{$this->prefix}_exp", $breakpoint[$exp], $this->ttl);
            $this->cache->put("{$this->prefix}_breakpoints", json_encode($breakpoints), $this->ttl);
        }

        return $output;
    }

    protected function resolveTagName(\DOMNode $node): string
    {
        $tagName = $node->tagName;

        if ('action' !== strtolower($tagName)) {
            return Str::studly("{$tagName}Tag");
        }

        $tagName = $node->attributes->getNamedItem('name')->nodeValue;

        return Str::studly("{$tagName}Action");
    }

    protected function resolveTagClass(string $tagName): string
    {
        $config = Container::getInstance()->make('config');

        $tagNs = config('ussd.tag-ns', []);
        $actionNs = config('ussd.action-ns', []);

        $namespaces = array_merge($tagNs, $actionNs);

        $fqcn = $tagName;

        foreach ($namespaces as $ns) {
            $fqcn = "{$ns}\\{$tagName}";
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        throw new \Exception("Missing class: {$tagName}");
    }

    protected function createTag(string $tagName, array $args = []): RenderableTag
    {
        $fqcn = $this->resolveTagClass($tagName);

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}