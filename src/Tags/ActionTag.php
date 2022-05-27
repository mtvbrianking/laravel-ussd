<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Support\Facades\Log;

class ActionTag extends BaseTag
{
    public function handle(): ?string
    {
        $actionName = $this->node->attributes->getNamedItem('name')->nodeValue;

        $className = Str::studly($actionName);
        $action = $this->createAction("Bmatovu\\Ussd\\Actions\\{$className}Action", [$this->cache, $this->prefix, $this->ttl]);
        $action($node);

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

    protected function createAction(string $fqcn, array $args = []): callable
    {
        if (! class_exists($fqcn)) {
            throw new \Exception("Missing class: {$fqcn}");
        }

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}
