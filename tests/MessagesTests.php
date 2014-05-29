<?php namespace Volnix\Flashy\Tests;

use Volnix\Flashy\Messages;
use Symfony\Component\HttpFoundation\Session\Session;

class MessagesTests extends \PHPUnit_Framework_TestCase {

	private $messages = null;

	public function __construct()
	{
		ob_start();
	}

	public function __destruct()
	{
		echo ob_get_clean();
	}

	public function setUp()
	{
		$this->messages = new Messages;
	}

	public function tearDown()
	{
		unset($this->messages);
	}

	public function testSetAsArray()
	{
		$data = ['error' => 'foo'];
		$this->messages->setAsArray($data);

		$messages = $this->messages->get('error');
		$this->assertTrue(is_array($messages));
		$this->assertEquals('foo', $messages[0]);
	}

	public function testSetSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$messages = $this->messages->get('error');
		$this->assertTrue(is_array($messages));
		$this->assertEquals('foo', $messages[0]);
		$this->assertEquals('bar', $messages[1]);
		$this->assertEquals('baz', $messages[2]);
	}

	public function testSetMessageNewSession()
	{
		unset($this->messages);
		$this->messages = new Messages(new Session);

		$this->messages->error('foo');
		$this->assertEquals('foo', $this->messages->get('error')[0]);
	}

	public function testGetClearsMessages()
	{
		$this->messages->error('foo');
		$messages = $this->messages->get('error');
		$this->assertTrue(is_array($messages));
		$this->assertEquals('foo', $messages[0]);

		$messages = $this->messages->get('error');
		$this->assertTrue(is_array($messages));
		$this->assertEquals(0, count($messages));
	}

	public function testClear()
	{
		$this->messages->error('foo');
		$this->assertEquals('foo', $this->messages->get('error')[0]);
		$this->messages->clear();
		$this->assertTrue(is_array($this->messages->get('error')));
		$this->assertEquals(0, count($this->messages->get('error')));
	}

	public function  testClearSession()
	{
		$this->messages->error('foo')->saveData();
		$this->assertEquals('foo', (new Session)->get(Messages::SESSION_INDEX)['error'][0]);

		$this->messages->clear(true);
		$this->assertNotEquals('foo', (new Session)->get(Messages::SESSION_INDEX)['error'][0]);
	}

	public function testGetFormattedSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$message = $this->messages->getFormatted('error');

		$this->assertTrue(is_string($message));
		$this->assertRegExp('/alert alert\-danger/', $message);
	}

	public function testGetComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);

		$errors = $this->messages->get('error');
		$infos = $this->messages->get('info');

		$this->assertTrue(is_array($errors));
		$this->assertEquals('foo', $errors[0]);

		$this->assertTrue(is_array($infos));
		$this->assertEquals('bip', $infos[0]);
	}

	public function testgetFormattedComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);

		$errors = $this->messages->getFormatted('error');
		$infos = $this->messages->getFormatted('info');

		$this->assertTrue(is_string($errors));
		$this->assertRegExp('/alert alert\-danger/', $errors);
		$this->assertRegExp('/\<li\>foo\<\/li\>/', $errors);

		$this->assertTrue(is_string($infos));
		$this->assertRegExp('/alert alert\-info/', $infos);
		$this->assertRegExp('/\<li\>bop\<\/li\>/', $infos);
	}

	public function testgetFormattedSimpleClassOverride()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$errors = $this->messages->getFormatted('error', ['error' => 'bip']);

		$this->assertTrue(is_string($errors));
		$this->assertRegExp('/class\="bip"/', $errors);
	}

	public function testGetNonExistentMessageType()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$this->assertTrue(is_array($this->messages->get('bad_type')));
		$this->assertEquals(0, count($this->messages->get('bad_type')));

		$this->assertTrue(is_string($this->messages->getFormatted('bip')));
		$this->assertEquals("", $this->messages->getFormatted('bip'));
	}

	public function testGetAllMessages()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);

		$all = $this->messages->get();

		$this->assertTrue(is_array($all));
		$this->assertEquals(2, count($all));
		$this->assertTrue(is_array($all['error']));
		$this->assertEquals(3, count($all['error']));
		$this->assertTrue(is_array($all['info']));
		$this->assertEquals(3, count($all['info']));
	}

	public function testGetAllFormattedMessages()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);

		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);

		$output_should_be = '<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz</li></ul></div><div class="alert alert-info"><ul><li>bip</li><li>bap</li><li>bop</li></ul></div>';

		$output = $this->messages->getFormatted();
		$this->assertTrue(is_string($output));
		$this->assertEquals($output_should_be, $output);
	}

	public function testNestedGet()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);

		$errors = $this->messages->get('error');

		$this->assertTrue(is_array($errors));
		$this->assertTrue(is_array($errors['baz']));
		$this->assertEquals('bip', $errors['baz'][0]);
		$this->assertEquals('bap', $errors['baz'][1]);
	}

	public function testNestedGetFomattedMessages()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);

		$output = $this->messages->getFormatted('error');

		$this->assertTrue(is_string($output));
		$this->assertEquals('<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz<ul><li>bip</li><li>bap</li><li>bop</li></ul></li></ul></div>', $output);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testBadMessageTypeThrowsException()
	{
		$this->messages->error(new \stdClass);
	}

	public function testRequestLifecycle()
	{
		// request starts
		$this->messages->error('foo');

		// request ends
		unset($this->messages);

		// next request starts
		$this->messages = new Messages;
		$messages = $this->messages->get('error');
		$this->assertEquals('foo', $messages[0]);

		// that request ends
		unset($this->messages);

		// final request starts
		$this->messages = new Messages;
		$messages = $this->messages->get('error');
		$this->assertTrue(is_array($messages));
		$this->assertEquals(0, count($messages));
	}
}