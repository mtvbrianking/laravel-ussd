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
    - [Example](#example)
    - [Validation](#validation)
    - [Simulator](#simulator)
- [Constructs](#constructs)
    - [Variable](#variable)
    - [Question](#question)
    - [Response](#response)
    - [Options](#options)
    - [If](#if)
    - [Choose](#choose)
    - [Action](#action)
    - [List](#list)
- [Advanced](#advanced)
    - [Cache](#cache)
    - [Parser](#parser)
    - [Simulator](#simulator)
    - [JSON](#json)
- [Testing](#testing)
- [Security](#security)
- [Contribution](#contribution)
- [Alternatives](#alternatives)
- [License](#license)

## Overview

Build USSD menus with ease. 

Instead of having tonnes of nested, complex PHP files, this package give you the ability to construct your menus in XML and execute them as though they were plain PHP files.

This approach greatly shrinks the code footprint as well as increase readability. 

Let's see an example for a simple would be SACCO USSD application.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<menu name="sacco">
    <action name="check-user"/>
    <options header="SACCO Services" noback="no">
        <option text="Savings">
            <list header="Saving Accounts" provider="saving-accounts" prefix="account"/>
            <options header="Savings">
                <option text="Deposit">
                    <options header="Deposit From:">
                        <option text="My Number">
                            <variable name="sender" value="{{phone_number}}"/>
                        </option>
                        <option text="Another Number">
                            <question name="sender" text="Enter Phone Number: "/>
                        </option>
                    </options>
                    <question name="amount" text="Enter Amount: "/>
                    <action name="deposit"/>
                </option>
                <option text="Withdraw">
                    <options header="Withdraw To:">
                        <option text="My Number">
                            <variable name="receiver" value="{{phone_number}}"/>
                        </option>
                        <option text="Another Number">
                            <question name="receiver" text="Enter Phone Number: "/>
                        </option>
                    </options>
                    <question name="amount" text="Enter Amount: "/>
                    <action name="withdraw"/>
                </option>
                <option text="Check Balance">
                    <action name="check-balance" text="To see your balance, enter PIN: "/>
                </option>
                <option text="Check Transaction">
                    <question name="transaction_id" text="Enter Transaction ID: "/>
                    <action name="check-transaction"/>
                </option>
            </options>
        </option>
        <option text="Loans">
            <response text="Coming soon."/>
        </option>
    </options>
</menu>
```

## Installation

Install the package via the Composer.

```bash
composer require bmatovu/laravel-ussd
```
### Configurations

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="config"
```

## Usage

### Example

> menus/menu.xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<menu name="demo">
    <question name="guest" text="Enter Name: "/>
    <response text="Hello {{guest}}."/>
</menu>
```

```php
use Bmatovu\Ussd\Ussd;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @see https://developers.africastalking.com/docs/ussd/overview
 */
class UssdController extends Controller
{
    public function __invoke(Request $request): Response
    {
        try {
            $ussd = new Ussd('menu.xml', $request->session_id);

            $output = $ussd->handle($request->text);
        } catch(\Exception $ex) {
            return response('END ' . $ex->getMessage());
        }

        return response('CON ' . $output);
    }
}
```

### Validation

Publish the menu schema (optional). 
Defaults to using the schema bundled within the package if none is present in your menus path, usually `menus/menu.xsd`.

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="schema"
```

Validate your menu files against the schema

```bash
php artisan ussd:validate
```

### Simulator

The package comes with a CLI USSD simulator supporting a handful of populator aggregators.

Publish the simulator config file to get started. Update the aggregator and the USSD service endpoint in the config file.

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="simulator"
```
Usage:

```bash
./vendor/bin/ussd --help
./vendor/bin/ussd 256772100103
```

__If you're an aggregator missing from the current list reachout to have you added. Or simply send a pull request__

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

Options are like named grouped `if, else-if` statements that allow a user to navigate to a predefined path.

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

The action tag gives you the ability to perform more customized operations.

```php
$userInfo = \App\Ussd\Actions\GetUserInfoAction('256732000000');
```

```xml
<!-- Actions can access all variable in cache -->
<action name="get-user-info"/>
<!-- Pass by value -->
<action name="get-user-info" msisdn="256732000000"/>
<!-- Pass by reference -->
<action name="get-user-info" msisdn="{{msisdn}}"/>
```

Note: Actions behave just like the normal tag i.e they can take input from a user or cache, and may or may not return output.

### List

List are showing dynamic items. E.g: user accounts fetched on demand.

Provider is the class providing the list of items. Each item must container an `id` and a `label`.

```php
$listItems = (new \App\Ussd\Providers\SavingAccountsProvider)->load();

[
    [
        'id' => 4364852, // account_id 
        'label' => '01085475262', // account_number
    ],
];
```

```xml
<list header="Saving Accounts" provider="saving-accounts" prefix="account"/>
```

## Advanced

### Cache

This package persists USSD session data in cache. Each key is prefixed with the `session_id` and it automatically expires after the configured `ttl`.

**Accessing variables**

```php
<variable name="color" value="blue"/>

$this->store->get('color'); // blue

Cache::store($driver)->get("{$sessionId}color"); // blue
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

### Parser

**Save default variables**

Example for saving any variable from the incoming USSD request.

```php
(new Ussd($menu, $request->session_id))
    // ->save($request->all())
    ->save([
        'phone_number' => $request->phone_number,
        'network_code' => $request->network_code,
    ]);
```

**Use custom menu entry point**

By default the parsing starts at the 1st element in you menu file, i.e `/menu/*[1]`.

If you wish to start from a different point or using a custom menu file structure. Here's how to go about it...

```php
(new Ussd($menu, $request->session_id))
    ->entry("/menus/menu[@name='sacco']/*[1]");
```

See: [xpath playground](http://xpather.com)

### Simulator

You can extend the USSD simulator with your aggregator of choice by simply registering it in the simulator config file.

The provider class should implement `Bmatovu\Ussd\Contracts\Aggregator`.

> simulator.json

```diff
  {
+     "aggregator": "hubtel",
      "aggregators": {
+         "hubtel": {
+             "provider": "App\\Ussd\\Simulator\\Hubtel",
+             "uri": "http://localhost:8000/api/ussd/hubtel",
+             "service_code": "*123#"
+         }
      }
  }
```

### JSON

Why use XML 🥴 and not JSON ☺️?

Compare the snippets below...

```xml
<menu name="demo">
    <question name="guest" text="Enter Name: "/>
    <response text="Hello {{guest}}."/>
</menu>
```

```json
{
    "@name": "demo",
    "question": {
        "@name": "guest",
        "@text": "Enter Name:"
    },
    "response": {
        "@text": "Hello {{guest}}."
    }
}
```

XML is more suited for writing programming language like constructs. 
It's very easy to validate XML schemas.
XML is also more compact and readable 🥰.

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
