# Flashy

Simple wrapper around [Symfony's Session](http://symfony.com/doc/current/components/http_foundation/sessions.html) flashbag.

[![Build Status](https://travis-ci.org/volnix/flashy.png?branch=master)](https://travis-ci.org/volnix/flashy) [![Total Downloads](https://poser.pugx.org/volnix/flashy/downloads.png)](https://packagist.org/packages/volnix/flashy) [![Latest Stable Version](https://poser.pugx.org/volnix/flashy/v/stable.png)](https://packagist.org/packages/volnix/flashy)

There are two pieces to flashy: [Prefill Data](#prefill) and [Messages](#messages).

## <a name="prefill"></a>Prefill Data

Often you will want to pre-fill form data from the last request when there are errors with user input.  Flashy will accept input from any source, whether it was from POST/GET or a generated array.  It is using Symfony's [AutoExpireFlashBag](http://api.symfony.com/2.4/Symfony/Component/HttpFoundation/Session/Flash/AutoExpireFlashBag.html) container so data is cleared after every request whether it is read or not.

To set the form data is done by calling the `setFormData` method:

```php
use Symfony\Component\HttpFoundation\Request;
use Volnix\Flashy\FormData;

$request = Request::createFromGlobals();
$form_data = new FormData;
$form_data->setFormData($request->query->all());
```

You would then call `setValue` to retrieve data, optionally passing a default value to use if the key is not set.

```php
use Volnix\Flashy\FormData;

$form_data = new FormData;
$form_data->setFormData(['foo' => 'bar']);

echo $form_data->setValue('foo'); // bar
echo $form_data->setValue('bim', 'baz'); // baz
```

To empty the form data storage, call the `emptyFormData` function:

```php
use Volnix\Flashy\FormData;

$form_data = new FormData;
$form_data->setFormData(['foo' => 'bar']);

echo $form_data->setValue('foo'); // bar

$form_data->emptyFormData();
echo $form_data->setValue('foo', 'baz'); // baz
```

Finally, the constructor of the FormData class accepts a Symfony `SessionInterface` as its only argument.  This was done for unit testing purposes, but can be used if desired.

```php
use Volnix\Flashy\FormData;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

$session = new Session(new MockFileSessionStorage(), null, new AutoExpireFlashBag());
$form_data = new FormData($session);
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

To retrieve them as an array, use the `getMessages` function (if not arg is passed, it will return all messages:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');
$messages->info(['Message one', 'Message two']);

foreach ($messages->getMessages('error') as $error_message) {
	echo $error_message;
}

$all_messages = $messages->getMessages();
```

The real magic happen when you call the `getFormattedMessages` function:

```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');
$messages->info(['Message one', 'Message two']);

// print all the error messages:
echo $messages->getFormattedMessages('error');

// print all the messages:
echo $messages->getFormattedMessages();
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

To override the default Bootstrap alert syntax, pass an array of class overrides to the `getFormattedMessages` function:
```php
use Volnix\Flashy\Messages;

$messages = new Messages;
$messages->error('Oh no!');

echo $messages->getFormattedMessages('error', ['error' => 'bip']);
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

echo $messages->getFormattedMessages('error');
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