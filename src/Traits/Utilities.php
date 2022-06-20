<?php

namespace Bmatovu\Ussd\Traits;

use Bmatovu\Ussd\Contracts\RenderableTag;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Str;

trait Utilities
{
    public function __get(string $key)
    {
        return $this->{$key};
    }

    public function __set(string $key, $value)
    {
        $this->{$key} = $value;
    }

    protected function fileToXpath(string $menuFile): \DOMXPath
    {
        if (! file_exists($menuFile)) {
            $menuFile = menu_path($menuFile);
        }

        $doc = new \DOMDocument();

        $doc->load($menuFile);

        return new \DOMXPath($doc);
    }

    protected function sessionExists(string $sessionId): bool
    {
        $preSessionId = $this->store->get('_session_id', '');

        return $preSessionId === $sessionId;
    }

    protected function clean(string $code = ''): string
    {
        return trim(trim($code, '*'), '#');
    }

    protected function getAnswer(?string $userInput): ?string
    {
        if ('' === (string) $userInput) {
            return '';
        }

        $preAnswer = $this->store->get('_answer', '');

        $answer = $this->clean(str_replace($preAnswer, '', $userInput));

        if ('' === (string) $answer) {
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
