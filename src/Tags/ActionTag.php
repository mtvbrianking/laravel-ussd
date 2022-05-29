<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActionTag extends BaseTag
{
    public function handle(): ?string
    {
        $actionName = $this->node->attributes->getNamedItem('name')->nodeValue;

        $className = Str::studly($actionName);
        $action = $this->createAction("{$className}Action", [$this->cache, $this->prefix, $this->ttl]);
        $action($this->node);

        // throw new \Exception($this->cache->get("{$this->prefix}_amount"));

        $pre = $this->cache->get("{$this->prefix}_pre");
        $exp = $this->cache->get("{$this->prefix}_exp", $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $this->cache->put("{$this->prefix}_pre", $exp, $this->ttl);
        $this->cache->put("{$this->prefix}_exp", $this->incExp($exp), $this->ttl);

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        return '';
    }

    public function process(?string $answer): void
    {
    }

    protected function resolveActionClass(string $actionName): string
    {
        $config = Container::getInstance()->make('config');

        $actionNs = config('ussd.action-ns');

        $fqcn = $actionName;

        foreach ($actionNs as $ns) {
            $fqcn = "{$ns}\\{$actionName}";
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        throw new \Exception("Missing class: {$actionName}");
    }

    protected function createAction(string $actionName, array $args = []): callable
    {
        $fqcn = $this->resolveActionClass($actionName);

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}
