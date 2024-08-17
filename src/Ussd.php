<?php

namespace Bmatovu\Ussd;

use Bmatovu\Ussd\Contracts\AnswerableTag;
use Bmatovu\Ussd\Support\Util;
use Bmatovu\Ussd\Traits\Utilities;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\App;

class Ussd
{
    use Utilities;

    protected \DOMXPath $xpath;
    protected string $sessionId;
    protected Store $store;
    protected bool $newSession = false;

    /**
     * @param \DOMXPath|string $menu
     */
    public function __construct($menu, string $sessionId)
    {
        $this->xpath = $menu instanceof \DOMXPath ? $menu : $this->fileToXpath($menu);

        $config = Container::getInstance()->make(ConfigRepository::class);
        $store = $config->get('ussd.cache.store', 'file');
        $ttl = $config->get('ussd.cache.ttl', 120);
        $this->store = new Store($store, $ttl, $sessionId);

        App::setLocale($this->store->get('locale', 'en'));

        if ($this->sessionExists($sessionId)) {
            return;
        }

        $this->newSession = true;
        $this->store->put('_session_id', $sessionId);
        $this->store->put('_answer', '');
        $this->store->put('_pre', '');
        $this->store->put('_exp', '/menu/*[1]');
        $this->store->put('_breakpoints', '[]');
    }

    /**
     * @param \DOMXPath|string $menu
     */
    public static function make($menu, string $sessionId): self
    {
        return new static($menu, $sessionId);
    }

    public static function new(...$args): self
    {
        return new static(...$args);
    }

    public function entry(string $expression): self
    {
        if ($this->newSession) {
            $this->store->put('_exp', $expression);
        }

        return $this;
    }

    public function save(array $options): self
    {
        foreach ($options as $key => $value) {
            $this->store->put($key, $value);
        }

        return $this;
    }

    public function handle(?string $userInput = ''): string
    {
        $answer = $this->getAnswer($userInput);

        if ($this->newSession) {
            $inquiry = $this->doParse();

            if (! $answer) {
                return $inquiry;
            }
        }

        $answers = explode('*', $answer);

        foreach ($answers as $answer) {
            $inquiry = $this->doParse($answer);
        }

        return $inquiry;
    }

    protected function doParse(?string $answer = ''): ?string
    {
        $this->doProcess($answer);

        $exp = $this->store->get('_exp');
        $node = $this->xpath->query($exp)->item(0);

        if (! $node) {
            $this->doBreak();
        }

        $inquiry = $this->doRender();

        if (! $inquiry) {
            return $this->doParse($answer);
        }

        return $inquiry;
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
            throw new \Exception(Util::hydrate($this->store, trans('MissingTag')));
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
        $inquiry = $tag->handle();

        $exp = $this->store->get('_exp');
        $breakpoints = (array) json_decode((string) $this->store->get('_breakpoints'), true);

        if ($breakpoints && isset($breakpoints[0][$exp])) {
            $breakpoint = array_shift($breakpoints);
            $this->store->put('_exp', $breakpoint[$exp]);
            $this->store->put('_breakpoints', json_encode($breakpoints));
        }

        return $inquiry;
    }
}
