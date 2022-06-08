![](./art/banner.png)

[![License](https://poser.pugx.org/bmatovu/laravel-ussd/license)](https://packagist.org/packages/bmatovu/laravel-ussd)
[![Unit Tests](https://github.com/mtvbrianking/laravel-ussd/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-ussd/actions?query=workflow:run-tests)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Documentation](https://github.com/mtvbrianking/laravel-ussd/workflows/gen-docs/badge.svg)](https://mtvbrianking.github.io/laravel-ussd)

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
    - [Configurations](#configurations)
- [Usage](#usage)
    - [Cache](#cache)
    - [Example](#example)
    - [Parser](#parser)
    - [Simulator](#simulator)
- [Constructs](#constructs)
    - [Variable](#variable)
    - [Question](#question)
    - [Response](#response)
    - [Options](#options)
    - [If](#if)
    - [Choose](#choose)
    - [Action](#action)
- [Advanced](#advanced)
    - [Cache](#cache)
- [Testing](#testing)
- [Security](#security)
- [Contribution](#contribution)
- [Alternatives](#alternatives)
- [License](#license)

## Overview

Build USSD menus with ease. 

Instead of having tonnes of nested, complex PHP files, this package give you the ability to construct your menus in XML and execute them as though they were plain PHP files.

This approach greatly shrinks the code footprint as well as increase readability.

## Installation

Install the package via the Composer.

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

The package does not do any garbage collection, i.e the cache entries will expire automatically when the set TTL (Time To Live) elapses.

If need be, clear the session data on 'flow break'.

### Example

> ../storage/app/demo.xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<menu>
    <response text="Hello World."/>
</menu>
```

```php
use Bmatovu\Ussd\Parser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * @see https://developers.africastalking.com/docs/ussd/overview
 */
class UssdController extends Controller
{
    public function __invoke(Request $request): Response
    {
        try {
            $demo = Storage::path('demo.xml');

            $parser = (new Parser($demo, '/menu/*[1]', $request->session_id));

            $output = $parser->parse($request->text);
        } catch(\Exception $ex) {
            return response('END ' . $ex->getMessage());
        }

        return response('CON ' . $output);
    }
}
```

### Parser

### **Parameters**

The parser takes in an array of the following options...

| Param        | Is Required | Description |
| ------------ | :---------: | ----------- |
| xpath        | yes         | DOMXPath for your menus. |
| expression   | yes         | Query to 1st executable tag in your XML menu. [Playground](http://xpather.com) |
| session_id   | yes         | Unique per session, not request. |

### Simulator

```bash
./vendor/bin/ussd 0790123123
./vendor/bin/ussd 0790123123 --dail 209
./vendor/bin/ussd 0790123123 --dail 209*4*5
```

## Constructs

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

## Advanced

### Cache

**Accessing variables**

```php
<variable name="color" value="blue"/>

$this->store->get('color'); // blue
```

**Reusing existing variables**

```xml
<variable name="msg" value="Bye bye."/>

<response text="{{msg}}"/> <!-- Bye bye -->
```

**Manual injection**

```php
$this->store->put('color', 'pink');
```

## Testing

To run the package's unit tests, run the following command:

``` bash
composer test
```

## Security

If you find any security related issues, please contact me directly at [mtvbrianking@gmail.com](mailto:mtvbrianking@gmail.com) to report it.

## Contribution

If you wish to make any changes or improvements to the package, feel free to make a pull request.

Note: A contribution guide will be added soon.

## Alternatives

- [sparors/laravel-ussd](https://github.com/sparors/laravel-ussd) takes a completely different approach on building USSD menus.

## License

The MIT License (MIT). Please see [License file](license.txt) for more information.
