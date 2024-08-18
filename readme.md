![](./art/banner.png)

[![License](https://poser.pugx.org/bmatovu/laravel-ussd/license)](https://packagist.org/packages/bmatovu/laravel-ussd)
[![Unit Tests](https://github.com/mtvbrianking/laravel-ussd/workflows/run-tests/badge.svg)](https://github.com/mtvbrianking/laravel-ussd/actions?query=workflow:run-tests)
[![Code Quality](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mtvbrianking/laravel-ussd/?branch=master)
[![Documentation](https://github.com/mtvbrianking/laravel-ussd/workflows/gen-docs/badge.svg)](https://mtvbrianking.github.io/laravel-ussd)

## Table of Contents

- [Overview](#overview)
- [Getting started](#getting-started)
- [Usage](#usage)
  * [Example](#example)
  * [Menu validation](#menu-validation)
  * [Simulator](#simulator)
- [Constructs](#constructs)
  * [Variable](#variable)
  * [Question](#question)
  * [Response](#response)
  * [Options](#options)
  * [If](#if)
  * [Choose](#choose)
  * [Action](#action)
  * [List](#list)
- [Advanced](#advanced)
  * [Exceptions](#exceptions)
  * [Retries](#retries)
  * [Comparisons](#comparisons)
  * [Localization](#localization)
  * [Cache](#cache)
  * [Parser](#parser)
  * [Simulation](#simulation)
  * [JSON](#json)
- [Testing](#testing)
- [Security](#security)
- [Contribution](#contribution)
- [Alternatives](#alternatives)
- [License](#license)

## Overview

Effortlessly construct intricate USSD menus with streamlined efficiency by replacing convoluted nests of PHP files with the simplicity of XML-based menu construction. This approach allows for seamless execution similar to standard PHP scripts, minimizing code complexity and  enhancing readability.

Let's explore an example of a simple SACCO USSD application.

```xml
<menu name="sacco">
    <action name="check_user" />
    <options header="SACCO Services" noback="no">
        <option text="Savings">
            <list header="Saving Accounts" provider="saving_accounts" prefix="account" />
            <options header="Savings">
                <option text="Deposit">
                    <options header="Deposit From:">
                        <option text="My Number">
                            <variable name="sender" value="{{phone_number}}" />
                        </option>
                        <option text="Another Number">
                            <question name="sender" text="Enter Phone Number: " />
                        </option>
                    </options>
                    <question name="amount" text="Enter Amount: " />
                    <action name="deposit" />
                </option>
                <option text="Withdraw">
                    <options header="Withdraw To:">
                        <option text="My Number">
                            <variable name="receiver" value="{{phone_number}}" />
                        </option>
                        <option text="Another Number">
                            <question name="receiver" text="Enter Phone Number: " />
                        </option>
                    </options>
                    <question name="amount" text="Enter Amount: " />
                    <action name="withdraw" />
                </option>
                <option text="Check Balance">
                    <action name="check_balance" text="To see your balance, enter PIN: " />
                </option>
                <option text="Check Transaction">
                    <question name="transaction_id" text="Enter Transaction ID: " />
                    <action name="check_transaction" text="To check transaction, enter PIN: " />
                </option>
            </options>
        </option>
        <option text="Loans">
            <response text="Coming soon." />
        </option>
    </options>
</menu>
```

## Getting started

**Installation**

```bash
composer require bmatovu/laravel-ussd
```

**Publishables**

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider"
```

## Usage

### Example

> menus/menu.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<menu name="demo">
    <question name="guest" text="Enter Name: " />
    <response text="Hello {{guest}}." />
</menu>
```

> app/Http/Controller/Api/UssdController

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Bmatovu\Ussd\Exceptions\FlowBreakException;
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
            $output = Ussd::make('menu.xml', $request->sessionId)->handle($request->text);
        } catch(FlowBreakException $ex) {
            return response('END ' . $ex->getMessage());
        } catch(\Exception $ex) {
            return response('END ' . get_class($ex));
        }

        return response('CON ' . $output);
    }
}
```

> routes/api.php

```php
use App\Http\Controllers\Api\UssdController;
use Illuminate\Support\Facades\Route;

Route::post('/ussd', [UssdController::class, '__invoke']);
```

See more examples in the [demo repo](https://github.com/mtvbrianking/ussd-demo/tree/master/app/Http/Controllers/Api)

### Menu validation

**The Schema**

You can publish the default schema with the following command:

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="ussd-schema"
```

To ensure your menu files conform to the schema, you can validate them with the following command:

```bash
php artisan ussd:validate
```

**VSCode integration**

For real-time XSD validations and suggestions, you can use the [RedHat XML extension](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-xml) in Visual Studio Code. This extension provides helpful features for working with XML schemas, including syntax highlighting and validation.

```diff
<?xml version="1.0" encoding="UTF-8"?>
- <menu name="demo">
+ <menu name="demo"
+     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
+     xsi:noNamespaceSchemaLocation="menu.xsd">
      <question name="guest" text="Enter Name: " />
      <response text="Hello {{guest}}." />
  </menu>
```

### Simulator

The package includes a CLI USSD simulator that supports several popular aggregators.

To get started, publish the simulator configuration file and update it with your aggregator and USSD service endpoint.

```bash
php artisan vendor:publish --provider="Bmatovu\Ussd\UssdServiceProvider" --tag="ussd-simulator"
```
Usage:

```bash
./vendor/bin/ussd --help
./vendor/bin/ussd 256772100103
```

**Aggregators**

- Africastalking
- Comviva (Airtel & MTN)

_If your aggregator is not listed, you can request its addition by contacting us or by submitting a pull request._

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

**Note**: this tag throws a `FlowBreakException` to mark a break in the normal flow.

### Options

Options are like named grouped `if, else-if` statements that allow a user to navigate to a predefined path.

```php
$choice = readline('Choose service [1. Deposit, 2. Withdraw]: ');

if($choice === 1) {
    // deposit...
} elseif($choice === 2) {
    // withdraw...
}
```

```xml
<options header="Choose service">
    <option text="Deposit">
        <!-- ... -->
    </option>
    <option text="Withdraw">
        <!-- ... -->
    </option>
</options>
```

**Disable backward navigation**

By default, a `0) Back` option is added to the rendered options. To disable this behaviour, use the `noback` attribute.

Note: _Top-level options should use the `noback` attribute as there’s no previous level to return to._

```xml
<options header="Choose service" noback="no">
    <!-- ... -->
</options>
```

### If

It can contain any other tags, including nested `<if>` tags.

```php
if($role == 'treasurer') {
    // ...
}
```

```xml
<if key="role" value="treasurer">
    <!-- ... -->
</if>
```

### Choose

This construct is intended to handle scenarios typically covered by `if`, `else if`, `else`, and `switch` statements.

**Example #1**

```php
if($role == 'treasurer') {
    // ...
} else {
    // ...
}
```

```xml
<choose>
    <when key="role" value="treasurer">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

**Example #2**

```php
if($role == 'treasurer') {
    // ...
} elseif($role == 'member') {
    // ...
} else {

}
```

```xml
<choose>
    <when key="role" value="treasurer">
        <!-- ... -->
    </when>
    <when key="role" value="member">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

**Example #3**

```php
switch ($role) {
    case "treasurer":
        // ...
        break;
    case "member":
        // ...
        break;
    default:
        // ...
}
```

```xml
<choose>
    <when key="role" value="treasurer">
        <!-- ... -->
    </when>
    <when key="role" value="memeber">
        <!-- ... -->
    </when>
    <otherwise>
        <!-- ... -->
    </otherwise>
</choose>
```

### Action

Action tags enable you to execute more tailored operations.

```php
$userInfo = \App\Ussd\Actions\GetUserInfoAction('256732000000');
```

**Arguments**

You can provide arguments for these actions either through attributes or by defining variables.

```xml
<!-- Read from cache -->
<!-- $msisdn = $this->store->get('msisdn'); -->
<action name="get_user_info"/>

<!-- Pass as attribute -->
<action name="get_user_info" msisdn="{{msisdn}}"/>

<!-- Pass as variable -->
<action name="get_user_info">
    <variable name="msisdn" value="{{msisdn}}"/>
</action>
```

**Getting user input**

When the `text` attribute is included in an action tag, it prompts the user for input in the same way that a `<question>` tag would.

```xml
<!-- Approach #1 - user input handled by a qn tag -->
<question name="pin" text="To check balance, enter PIN: "/>
<action name="validate_pin"/>

<!-- Approach #2 - user input handled by the action -->
<action name="validate_pin" text="To check balance, enter PIN: "/>
```

### List

Lists are designed to show dynamic items.

To use this feature, your provider must supply a list where each item includes both an `id` (a unique identifier) and a `label` (the text displayed to the user).

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
<list header="Saving Accounts" provider="saving_accounts" prefix="account"/>
```

Accessing the selected item on the list

```xml
<!-- Format: {prefix}_<id, label> -->
<response text="{{account_id}}"/><!-- 4364852 -->
<response text="{{account_label}}"/><!-- 01085475262 -->
```

**Note**: Similar to actions, you can pass arguments to lists via attributes or as variables.

## Advanced

### Exceptions

The `<response>` tag throws a `FlowBreakException` which MUST be handled in your controller to manage the flow of the USSD interaction.

Additionally, you can catch other exceptions and optionally translate them into user-friendly messages, as demonstrated below...

```php
try {
    $output = Ussd::make('menu.xml', $request->session_id)
        ->handle($request->text);
} catch(FlowBreakException $ex) {
    return response('END ' . $ex->getMessage());
} catch(\Exception $ex) {
    // return response('END ' . get_class($ex));
    return response('END ' . trans(get_class($ex)));
}

return response('CON ' . $output);
```

> resources/lang/en.json

```json
{
    "RequestException": "Sorry, we failed to process your request.",
    "TimeoutException": "Your request has timed out.",
    "AuthenticationException": "Invalid user credentials.",
    "AuthorizationException": "You are not authorized to perform this action."
}
```

Note: _To minimize logging, the FlowBreakException should not be reported by your application. [Ref](https://laravel.com/docs/11.x/errors#ignoring-exceptions-by-type)_

### Retries

You can also configure the number of retry attempts and specify a custom error message.

**Question**

Using regex patterns.

```diff
  <question
      name="pin"
      text="Enter PIN: "
+     retries="1"
+     pattern="^[0-9]{5}$"
+     error="You entered the wrong PIN. Try again" />
```

**Options & Lists**

Validation can also be performed based on the available options in the list.

```diff
  <options
      header="Choose a test"
+     retries="1"
+     error="Choose the correct number:">
      ...
  </option>
```

```diff
  <list 
      header="Saving Accounts" 
      provider="saving_accounts" 
      prefix="account" 
+     retries="1"
+     error="Choose the correct number:"/>
```

**Note**: Using retries in `<action>` tags is not recommended because these tags do not have visibility into the context provided by preceding tags.

### Comparisons

The `<if>` and `<when>` tags support various types of comparisons.

If a comparison condition (`cond`) is not specified or is unsupported, the default comparison is `eq` (equals)

```xml
<if key="age" value="18">
<if key="age" cond="eq" value="18">
```

| Type | Conditions |
| :--- | :--------- |
| Numbers | - lt<br/>- gt<br/>- lte<br/>- gte<br/>- eq<br/>- ne<br/>- btn |
| Strings | - str.equals<br/>- str.not_equals<br/>- str.starts<br/>- str.ends<br/>- str.contains |
| Regex | - regex.match |
| Arrays | - arr.in<br/>- arr.not_in |
| Dates | - date.equals<br/>- date.before<br/>- date.after<br/>- date.between |
| Time | - time.equals<br/>- time.before<br/>- time.after<br/>- time.between |

### [Localization](https://laravel.com/docs/11.x/localization)

Create translation files within your project and reference the keys in your menu files. Here’s an example:

> menus/menu.xml

```xml
<menu name="demo">
    <action name="set_locale" locale="fr" />
    <question name="guest" text="AskForName" />
    <response text="GreetGuest" />
</menu>
```

> resources/lang/fr.json

```json
{
    "AskForName": "Entrez le nom:",
    "GreetGuest": "Boujour {{guest}}"
}
```

> USSD simulation

```
ussd-demo$ vendor/bin/ussd 250723000123
Entrez le nom: 
John

Boujour John
```

**Note**:
- use the `set_locale` action to change locale directly from the USSD menu, and
- use `App::setLocale` to change the locale from your controller

### Cache

This package stores USSD session data in the cache. Each key is prefixed with the `session_id` and will automatically expire based on the configured `ttl` (time-to-live).

**Accessing variables**

```xml
<variable name="color" value="blue"/>
```

```php
$this->store->get('color'); // blue

Cache::store($driver)->get("{$sessionId}color"); // blue
```

**Reusing existing variables**

```xml
<variable name="msg" value="Bye bye."/>

<response text="{{msg}}"/> <!-- Bye bye -->
```

### Parser

**Save default variables**

Here's an example of how to save a variable from an incoming USSD request:

```php
Ussd::make($menu, $request->session_id)
    ->save([
        'phone_number' => $request->phone_number,
    ])
    ->handle(...);
```

**Use custom menu entry point**

By default, parsing begins at the first element in your menu file, which corresponds to `/menu/*[1]`.

If you want to start parsing from a different point or use a custom menu structure, you can specify the entry point in your code:

```php
Ussd::make($menu, $request->session_id)
    ->entry("/menus/menu[@name='sacco']/*[1]")
    ->handle(...);
```

See: [xpath playground](http://xpather.com)

### Simulation

You can enhance the USSD simulator by adding your preferred aggregator.

To do this, register the aggregator in the simulator configuration file. Ensure that the provider class implements the `Bmatovu\Ussd\Contracts\Aggregator` interface.

> simulator.json

```diff
  {
+     "aggregator": "africastalking",
      "aggregators": {
+         "africastalking": {
+             "provider": "App\\Ussd\\Simulator\\Africastalking",
+             "uri": "http://localhost:8000/api/ussd/africastalking",
+             "service_code": "*123#"
+         }
      }
  }
```

### JSON

XML is often preferred for constructing configurations that resemble programming logic due to its robust schema validation capabilities and its clear, hierarchical structure. XML’s format is particularly useful for complex configurations and data structures, as it maintains readability and provides straightforward validation against defined schemas.

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

## Testing

To run the package's unit tests, run the following command:

``` bash
composer test
```

## Security

If you discover any security-related issues, please report them directly to [mtvbrianking@gmail.com](mailto:mtvbrianking@gmail.com)

## Contribution

If you would like to contribute to the package by making changes or improvements, feel free to submit a pull request.

Note: _A detailed contribution guide may be added later._

## Alternatives

- [sparors/laravel-ussd](https://github.com/sparors/laravel-ussd) offers a different approach to building USSD menus.

## License

This package is licensed under the MIT License. For more details, please refer to the [License file](license.txt).
