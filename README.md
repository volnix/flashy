# Flashy

Simple wrapper around [Symfony's Session](http://symfony.com/doc/current/components/http_foundation/sessions.html) flashbag.

[![Build Status](https://travis-ci.org/volnix/flashy.png?branch=master)](https://travis-ci.org/volnix/flashy) [![Total Downloads](https://poser.pugx.org/volnix/flashy/downloads.png)](https://packagist.org/packages/volnix/flashy) [![Latest Stable Version](https://poser.pugx.org/volnix/flashy/v/stable.png)](https://packagist.org/packages/volnix/flashy)

There are two pieces to flashy: [Prefill Data](#prefill) and [Messages](#messages).

## <a name="prefill"></a>Prefill Data

Often you will want to pre-fill form data from the last request when there are errors with user input.  Flashy will accept input from any source, whether it was from POST/GET or a generated array.  It is using Symfony's [AutoExpireFlashBag](http://api.symfony.com/2.4/Symfony/Component/HttpFoundation/Session/Flash/AutoExpireFlashBag.html) container so data is cleared after every request whether it is read or not.

To set the form data is done by calling the `set` method:

```php
use Symfony\Component\HttpFoundation\Request;
use Volnix\Flashy\FormData;

$request = Request::createFromGlobals();
$form_data = new FormData;
$form_data->set($request->query->all());
```

You would then call `get` to retrieve data, optionally passing a default value to use if the key is not set.

```php
use Volnix\Flashy\FormData;

$form_data = new FormData;
$form_data->set(['foo' => 'bar']);

echo $form_data->get('foo'); // bar
echo $form_data->get('bim', 'baz'); // baz
```

To empty the form data storage, call the `clear` function:

```php
use Volnix\Flashy\FormData;

$form_data = new FormData;
$form_data->set(['foo' => 'bar']);

echo $form_data->set('foo'); // bar

$form_data->clear();
echo $form_data->get('foo', 'baz'); // baz
```

## <a name="messages"></a>Messages

The Messages class will make handling flash messages a breeze.  It is loosely tied to [Bootstrap 3](http://getbootstrap.com/) for its alert markup, but this can easily be overridden.  It is also using Symfony's [AutoExpireFlashBag](http://api.symfony.com/2.4/Symfony/Component/HttpFoundation/Session/Flash/AutoExpireFlashBag.html) container.

The [__call](http://www.php.net/manual/en/language.oop5.overloading.php#object.call) magic method is used to allow any type of messages for maximum portability.

To set a type of message, you may call any function, passing in a string or an array of messages, e.g.:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');
$messages->info(['Message one', 'Message two']);
```

You may also set messages as an array of [type => messages], e.g.:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->setAsArray(['error' => 'foo');
```

To retrieve them as an array, use the `get` function (if not arg is passed, it will return all messages:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');
$messages->info(['Message one', 'Message two']);

foreach ($messages->get('error') as $error_message) {
	echo $error_message;
}

$all_messages = $messages->get();
```

The real magic happen when you call the `getFormatted` function:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');
$messages->info(['Message one', 'Message two']);

// print all the error messages:
echo $messages->getFormatted('error');

// print all the messages:
echo $messages->getFormatted();
```

Formatted messages are printed in the following markup:

```html
<div class="alert alert-danger">
	<ul>
		<li>Message one</li>
		<li>Message two</li>
	</ul>
</div>
```

To override the default Bootstrap alert syntax, pass an array of class overrides to the `getFormatted` function:
```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');

echo $messages->getFormatted('error', ['error' => 'bip']);
```

will yield:

```html
<div class="bip">
	<ul>
		<li>Oh no!</li>
	</ul>
</div>
```

The Messages class also supports nesting of messages, e.g.:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error(['foo', 'bar' => ['bip', 'bap']]);

echo $messages->getFormatted('error');
```

will yield:
```html
<div class="bip">
	<ul>
		<li>foo</li>
		<li>
			bar
			<ul>
				<li>bip</li>
				<li>bap</li>
			</ul>
		</li>
	</ul>
</div>
```

To empty the messages storage, call the `clear` function:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->set(['foo' => 'bar']);

echo $messages->get('foo'); // bar

$messages->clear();
echo $messages->get('foo', 'baz'); // baz
```