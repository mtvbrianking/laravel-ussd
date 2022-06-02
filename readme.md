# Laravel USSD

[![License](https://poser.pugx.org/bmatovu/laravel-ussd/license)](https://packagist.org/packages/bmatovu/laravel-ussd)
[![Unit Tests](https://github.com/mtvbrianking/laravel-ussd/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-ussd/actions?query=workflow:run-tests)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Documentation](https://github.com/mtvbrianking/laravel-ussd/workflows/gen-docs/badge.svg)](https://mtvbrianking.github.io/laravel-ussd)

A minimalist package to help you build XML based USSD menus.

**Life-cycle**

The package uses cache to keep track of variables during an ongoing USSD session.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
    - [Configurations](#configurations)
- [Usage](#usage)
    - [Cache](#cache)
    - [Example](#example)
    - [Simulator](#simulator)
- [Flow](#flow)
- [Constucts](#constucts)
    - [Variable](#variable)
    - [Question](#question)
    - [Response](#response)
    - [Options](#options)
    - [If](#if)
    - [Choose](#choose)
    - [Action](#action)

## Overview

Build USSD menus with ease in XML.

## Installation

Install the package via the Composer package manager:

```bash
composer require bmatovu/laravel-ussd
```
### Configurations

Public the package configuration file.

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="config"
```

## Usage

### Cache

This package persists USSD session data in cache. Each key is prefixed with the `phone_number` and `service_code` to make it unique and avoid overriding data accidentally.

**Expiry**

The package does not do any garabge collection for you, i.e the cache entries will expire automatically when the set TTL (Time To Live) elapses.
If need be, clear the session data on 'flow break'.

### Example

> storage/ussd/example.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<menu>
    <response text="Hello World."/>
</menu>
```

```php
use Bmatovu\Ussd\Menus\Parser;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UssdController extends Controller
{
    const FC = 'continue';
    const FB = 'break';

    protected CacheContract $cache;

    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
    }
    
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $doc = new \DOMDocument();

            $doc->load(Storage::disk('local')->path('ussd/example.xml'));

            $xpath = new \DOMXPath($doc);

            $options = $request->only(['session_id', 'phone_number', 'service_code']);
            $options['expression'] = '/menu/*[1]';

            $parser = new Parser($xpath, $options, $this->cache, 120);

            $output = $parser->parse($request->answer);
        } catch(\Exception $ex) {
            return response()->json(['flow' => self::FB, 'data' => $ex->getMessage()]);
        }

        return response()->json(['flow' => self::FC, 'data' => $output]);
    }
}
```

### Simulator

\* to be developed

```bash
./vendor/bin/ussd 0790123123
./vendor/bin/ussd 0790123123 --dail 209*4*5
```

## Flow

USSD has basically 2 flow types namely; `continue` (default) and `break`.

Always assume the flow to be continuing unless you get an exception.

## Constucts

### Variable

```php
$color = 'blue';
```

```xml
<variable name="color" value="blue"/>
```

**Note**: This tag has no output

### Question

```php
$username = readline('Enter username: ');
```

```xml
<question name="username" text="Enter username: "/>
```

### Response

```php
exit('Thank you for banking with us.');
```

```xml
<response text="Thank you for banking with us."/>
```

**Note**: this tag throws an exception to mark a break in the normal flow.

### Options

Options are like named grouped `if-elseif` statements that allow a user to navigate to a predefined path.

```php
$choice = readline('Choose gender [1. Male, 2. Female]: ');

if($choice === 1) {
    // do male stuff
} elseif($choice === 2) {
    // Do female stuff
}
```

```xml
<options header="Choose gender">
    <option text="Male">
        <!-- ... -->
    </option>
    <option text="Female">
        <!-- ... -->
    </option>
</options>
```

**Disable backward navigation**

By default `0) Back` option will be added to the options rendered. Use the attribute `noback` to this able this behavior.

This behavior may only be used for nested `<options>` tags.

```xml
<options header="Choose gender" noback="no">
    <!-- ... -->
</options>
```

### If

Can container any other tags inclusive of the IF tag itself.

```php
if($gender == 'male') {
    // ...
}
```

```xml
<if key="gender" value="male">
    <!-- ... -->
</if>
```

### Choose

This construct should cover for `if-else`, `if-elseif-else`, and the native `switch`.

**Example #1**

```php
if($gender == 'male') {
    // ...
} else {
    // ...
}
```

```xml
<choose>
    <when key="gender" value="male">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

**Example #2**

```php
if($gender == 'male') {
    // ...
} elseif($gender == 'female') {
    // ...
} else {

}
```

```xml
<choose>
    <when key="gender" value="male">
        <!-- ... -->
    </when>
    <when key="gender" value="female">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

**Example #3**

```php
switch ($gender) {
    case "male":
        // ...
        break;
    case "female":
        // ...
        break;
    default:
        // ...
}
```

```xml
<choose>
    <when key="gender" value="male">
        <!-- ... -->
    </when>
    <when key="gender" value="female">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

### Action

This tag gives you the ability to perform more complex operations.

```php
$user = getUser($phone);
```

```xml
<!-- Actions can access all variable in cache -->
<action name="get-user"/>
<!-- Pass by value -->
<action name="get-user" phone="256732000000"/>
<!-- Pass by reference -->
<action name="get-user" phone="{{phone}}"/>
```

Note: Actions have no output. But they can manipulate (get/set) variables in cache.
