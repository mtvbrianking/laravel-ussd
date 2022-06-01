<?php

namespace Bmatovu\Ussd\Tags;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActionTag extends BaseTag
{
    public function handle(): ?string
    {
        $pre = $this->fromCache('pre');
        $exp = $this->fromCache('exp', $this->node->getNodePath());

        // Log::debug("CheckIn  -->", ['pre' => $pre, 'exp' => $exp]);

        $this->toCache('pre', $exp);
        $this->toCache('exp', $this->incExp($exp));

        // Log::debug("CheckOut -->", ['pre' => $exp, 'exp' => $this->incExp($exp)]);

        $actionName = $this->readAttr('name');

        $className = Str::studly($actionName);
        $action = $this->createAction("{$className}Action", [$this->node, $this->cache, $this->prefix, $this->ttl]);

        return $action->handle();
    }

    public function process(?string $answer): void
    {
        $actionName = $this->readAttr('name');

        $className = Str::studly($actionName);
        $action = $this->createAction("{$className}Action", [$this->node, $this->cache, $this->prefix, $this->ttl]);
        $action->process($answer);
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

    protected function createAction(string $actionName, array $args = []): object
    {
        $fqcn = $this->resolveActionClass($actionName);

        return \call_user_func_array([new \ReflectionClass($fqcn), 'newInstance'], $args);
    }
}
