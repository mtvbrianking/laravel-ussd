<?php

require __DIR__ . '/vendor/autoload.php';

use Doctum\Doctum;
use Doctum\RemoteRepository\GitHubRemoteRepository;
use Doctum\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = __DIR__ . '/src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('bin')
    ->exclude('tests')
    ->exclude('vendor')
    ->in($dir);

$versions = GitVersionCollection::create($dir)
    ->add('master', 'Master branch');

$repo = new GitHubRemoteRepository(
    'mtvbrianking/laravel-ussd',
    dirname($dir),
    'https://github.com/'
);

$options = [
    'theme' => 'default',
    'versions' => $versions,
    'title' => 'Laravel USSD',
    'build_dir' => __DIR__ . '/docs',
    'cache_dir' => __DIR__ . '/docs/cache',
    'remote_repository' => $repo,
    'default_opened_level' => 3,
];

return new Doctum($iterator, $options);
