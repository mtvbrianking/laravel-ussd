<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

if (!function_exists('menu_path')) {
    /**
     * Get the path to the menus directory.
     */
    function menu_path(string $path = ''): string
    {
        $app = Container::getInstance();

        $config = $app->make(ConfigRepository::class);

        $menusDir = $config->get('ussd.menu-path');

        $path = $path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path;

        return $app->basePath($menusDir . $path);
    }
}
