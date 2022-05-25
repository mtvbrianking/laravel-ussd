## Laravel Package Boilerplate.

[![Build Status](https://travis-ci.org/mtvbrianking/laravel-package-boilerplate.svg?branch=master)](https://travis-ci.org/mtvbrianking/laravel-package-boilerplate)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-package-boilerplate/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-package-boilerplate/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-package-boilerplate/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-package-boilerplate/?branch=master)
[![StyleCI](https://github.styleci.io/repos/230607368/shield?branch=master)](https://github.styleci.io/repos/230607368)
[![Documentation](https://img.shields.io/badge/Documentation-Blue)](https://mtvbrianking.github.io/laravel-package-boilerplate)

### [Installation](https://packagist.org/packages/bmatovu/laravel-package-boilerplate)

Install via composer package manager:

```bash
composer create-project --prefer-dist bmatovu/laravel-package-boilerplate hello-world
```

Alternatively, generate a Github repository using the `Use this template` call to action button or the link below...

> https://github.com/mtvbrianking/laravel-package-boilerplate/generate

### Own the package

Update the `composer.json` file to match your credentials.

Change the namespaces to match those you're using in `src`.

Change the type from `project` to `library`

```bash
composer dump-autoload
```

## Testing

We've defaulted to [Orchestra's testbench](https://github.com/orchestral/testbench)

```bash
composer test
```

### Code Style & Quality

We've added [StyleCI](https://styleci.io) configurations with the Laravel present to get you started.

Also added [ScrutinizerCI](https://scrutinizer-ci.com) configurations for code quality, test coverage inspection.

Locally, you can restort to [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer).

```bash
composer cs-fix
```

### Source code documentation

You need to download [Doctum](https://github.com/code-lts/doctum) for source code documentation.

```bash
composer doc
```

To auto deploy documentation; be sure to add a [`Github token`](https://github.com/settings/tokens) for authorization.

### Sharing the package

You may share your package via [Packagist](packagist.org)

## Useful resources

- [Laravel Package Development - Blog](https://laravelpackage.com)

- [Laravel Package Development - Documentation](https://laravel.com/docs/master/packages)

- [Travis CI + GitHub Pages - Automated deployment](https://www.youtube.com/watch?v=BFpSD2eoXUk)
