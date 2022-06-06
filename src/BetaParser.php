<?php

namespace Bmatovu\Ussd;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Contracts\RenderableTag;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Record
{
    protected CacheContract $cache;
    protected int $ttl;
    protected string $prefix;

    public function __construct(string $store, int $ttl, string $prefix)
    {
        $this->cache = Cache::store($store);
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    public function get(string $key, $default)
    {
        return $this->cache->get("{$this->prefix}{$key}", $default);
    }

    public function pull(string $key)
    {
        return $this->cache->pull("{$this->prefix}{$key}");
    }

    public function put(string $key, $value): void
    {
        $this->cache->put("{$this->prefix}{$key}", $value, $this->ttl);
    }

    public function append(string $key, string $extra): void
    {
        $value = $this->cache->get("{$this->prefix}{$key}");

        $this->cache->put("{$this->prefix}{$key}", "{$value}{$extra}", $this->ttl);
    }
}

trait ParserUtils
{
    protected function sessionExists(string $sessionId): bool
    {
        $preSessionId = $this->record->get('_session_id', '');

        return $preSessionId === $sessionId;
    }

    protected function clean(string $code = ''): string
    {
        if (! $code) {
            return $code;
        }

        return rtrim(ltrim($code, '*'), '#');
    }

    protected function getAnswer(?string $userInput): ?string
    {
        if (! $userInput) {
            return '';
        }

        $preAnswer = $this->record->get('_answer');
        if (! $preAnswer) {
            return (string) $userInput;
        }

        $answer = $this->clean(str_replace($preAnswer, '', $userInput));

        $this->record->put('_answer', $userInput);

        return $answer;
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
        // $config = Container::getInstance()->make('config');
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

    protected function instantiateTag(string $tagName, array $args = []): RenderableTag
    {
        $fqcn = $this->resolveTagClass($tagName);

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}

class BetaParser
{
    use ParserUtils;

    protected \DOMXPath $xpath;
    protected string $sessionId;
    protected Record $record;

    public function __construct(\DOMXPath $xpath, string $expression, string $sessionId, string $serviceCode = '')
    {
        $this->xpath = $xpath;

        // $config = Container::getInstance()->make('config');
        $store = config('ussd.cache.store');
        $ttl = config('ussd.cache.ttl');
        $this->record = new Record($store, $ttl, $sessionId);

        if ($this->sessionExists($sessionId)) {
            return;
        }

        $serviceCode = $this->cleanup($serviceCode);
        $this->record->put('_session_id', $sessionId);
        $this->record->put('_service_code', $serviceCode);
        $this->record->put('_answer', $serviceCode);
        $this->record->put('_pre', '');
        $this->record->put('_exp', $expression);
        $this->record->put('_breakpoints', '[]');
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $key => $value) {
            $this->record->put($key, $value);
        }

        return self;
    }

    public function parse(?string $userInput): string
    {
        $answers = explode('*', $this->getAnswer($userInput));

        foreach ($answers as $answer) {
            $output = $this->doParse($answer);
        }

        return $output;
    }

    protected function doParse(?string $answer): ?string
    {
        $this->doProcess($answer);

        $exp = $this->record->get('_exp');
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
        $pre = $this->record->get('_pre');

        if (! $pre) {
            return;
        }

        $preNode = $this->xpath->query($pre)->item(0);

        $tagName = $this->resolveTagName($preNode);
        $tag = $this->instantiateTag($tagName, [$preNode, $this->record]);

        if (! $tag instanceof AnswerableTag) {
            return;
        }

        $this->record->append('_answer', "*{$answer}");

        $tag->process($answer);
    }

    protected function doBreak(): void
    {
        $exp = $this->record->get('_exp');

        $breakpoints = (array) json_decode((string) $this->record->get('_breakpoints'), true);

        if (! $breakpoints || ! isset($breakpoints[0][$exp])) {
            throw new \Exception('Missing tag');
        }

        $breakpoint = array_shift($breakpoints);
        $this->record->put('_exp', $breakpoint[$exp]);
        $this->record->put('_breakpoints', json_encode($breakpoints));
    }

    protected function doRender(): ?string
    {
        $exp = $this->record->get('_exp');

        $node = $this->xpath->query($exp)->item(0);

        $tagName = $this->resolveTagName($node);
        $tag = $this->instantiateTag($tagName, [$node, $this->record]);
        $output = $tag->handle();

        $exp = $this->record->get('_exp');
        $breakpoints = (array) json_decode((string) $this->record->get('_breakpoints'), true);

        if ($breakpoints && isset($breakpoints[0][$exp])) {
            $breakpoint = array_shift($breakpoints);
            $this->record->put('_exp', $breakpoint[$exp]);
            $this->record->put('_breakpoints', json_encode($breakpoints));
        }

        return $output;
    }
}

/*
$parser = new Parser($xpath, $exp, $request->session_id, $request->serviceCode);

$parser = new Parser($xpath, $exp, $request->session_id, $request->serviceCode)
    ->setOptions([
        'phone_number' => $request->phone_number,
    ]);

$output = $parser->parse($request->input);
*/
