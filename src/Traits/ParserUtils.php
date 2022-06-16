<?php

namespace Bmatovu\Ussd\Traits;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;

trait ParserUtils
{
    public function __get(string $key)
    {
        return $this->{$key};
    }

    public function __set(string $key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * @see https://stackoverflow.com/q/413071/2732184
     * @see https://www.regextester.com/97707
     */
    public function translate(string $text, string $pattern = '/[^{{\}\}]+(?=}})/'): string
    {
        preg_match_all($pattern, $text, $matches);

        if (0 === \count($matches[0])) {
            return $text;
        }

        $replace_vars = [];

        foreach ($matches[0] as $match) {
            $var = Str::slug($match, '_');
            $replace_vars["{{{$match}}}"] = $this->store->get("{$prefix}{$var}", "{{$var}}");
        }

        return strtr($text, $replace_vars);
    }

    protected function xpathFromStr(string $file): \DOMXPath
    {
        $doc = new \DOMDocument();

        $doc->load($file);

        return new \DOMXPath($doc);
    }

    protected function sessionExists(string $sessionId): bool
    {
        $preSessionId = $this->store->get('_session_id', '');

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
        if (null === $userInput) {
            return '';
        }

        $preAnswer = $this->store->get('_answer', '');

        $answer = $this->clean(str_replace($preAnswer, '', $userInput));

        if (null === $answer) {
            return '';
        }

        if (! $preAnswer || Str::endsWith($preAnswer, '*')) {
            $this->store->append('_answer', "{$answer}*");
        } else {
            $this->store->append('_answer', "*{$answer}*");
        }

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
        $config = Container::getInstance()->make(ConfigRepository::class);
        $tagNs = $config->get('ussd.tag-ns', []);
        $actionNs = $config->get('ussd.action-ns', []);

        $namespaces = array_merge($tagNs, $actionNs);

        $fqcn = $tagName;

        foreach ($namespaces as $ns) {
            $fqcn = "{$ns}\\{$tagName}";
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        throw new \Exception("Missing tag {$tagName}.");
    }

    protected function instantiateTag(string $tagName, array $args = []): RenderableTag
    {
        $fqcn = $this->resolveTagClass($tagName);

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}
