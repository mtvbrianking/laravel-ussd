<?php

namespace Bmatovu\Ussd;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Cache;

class Store
{
    protected CacheContract $cache;
    protected int $ttl;
    protected string $prefix;

    public function __construct(string $driver, int $ttl, string $prefix)
    {
        $this->cache = Cache::store($driver);
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    public function __get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->cache->get("{$this->prefix}{$key}");
    }

    public function __set(string $key, $value)
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }

        $this->cache->put("{$this->prefix}{$key}", $value, $this->ttl);
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get("{$this->prefix}{$key}", $default);
    }

    public function pull(string $key)
    {
        return $this->cache->pull("{$this->prefix}{$key}");
    }

    public function put(string $key, $value): void
    {
        // dd(['key' => "{$this->prefix}{$key}", 'value' => $value]);
        $this->cache->put("{$this->prefix}{$key}", $value, $this->ttl);
    }

    public function append(string $key, string $extra): void
    {
        $value = $this->cache->get("{$this->prefix}{$key}");

        $this->cache->put("{$this->prefix}{$key}", "{$value}{$extra}", $this->ttl);
    }

    public function flush()
    {
        $this->cache->flush();
    }
}
