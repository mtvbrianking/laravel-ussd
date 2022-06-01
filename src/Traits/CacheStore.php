<?php

namespace Bmatovu\Ussd\Traits;

trait CacheStore
{
    public function toCache(string $key, $value): void
    {
        $this->cache->put("{$this->prefix}_{$key}", $value, $this->ttl);
    }

    public function fromCache(string $key, $default = null)
    {
        return $this->cache->get("{$this->prefix}_{$key}", $default);
    }
}
