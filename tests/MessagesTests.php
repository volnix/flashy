<?php namespace Volnix\Flashy\Tests;

use Volnix\Flashy\Messages;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcacheSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;

class MessagesTests extends \PHPUnit_Framework_TestCase {
	
	private $messages = null;
	private $save_path = "";
	private $session = null;
	
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
		$this->messages = new Messages();
	}
	
	public function tearDown()
	{
		// wipe our form data so we have a fresh object to work with
		$this->messages->clear();
	}
	
	public function testSetAsArray()
	{
		$data = ['error' => 'foo'];
		$this->messages->setAsArray($data);
		$this->assertTrue(is_array($this->messages->get('error')));
		$this->assertEquals('foo', $this->messages->get('error')[0]);
	}
	
	public function testSetSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->get('error')));
		$this->assertEquals('foo', $this->messages->get('error')[0]);
		$this->assertEquals('bar', $this->messages->get('error')[1]);
		$this->assertEquals('baz', $this->messages->get('error')[2]);
	}
	
	public function testgetFormattedSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormatted('error')));
		$this->assertRegExp('/alert alert\-danger/', $this->messages->getFormatted('error'));
	}
	
	public function testgetComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$this->assertTrue(is_array($this->messages->get('error')));
		$this->assertEquals('foo', $this->messages->get('error')[0]);
		
		$this->assertTrue(is_array($this->messages->get('info')));
		$this->assertEquals('bip', $this->messages->get('info')[0]);
	}
	
	public function testgetFormattedComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$this->assertTrue(is_string($this->messages->getFormatted('error')));
		$this->assertRegExp('/alert alert\-danger/', $this->messages->getFormatted('error'));
		$this->assertRegExp('/\<li\>foo\<\/li\>/', $this->messages->getFormatted('error'));
		
		$this->assertTrue(is_string($this->messages->getFormatted('info')));
		$this->assertRegExp('/alert alert\-info/', $this->messages->getFormatted('info'));
		$this->assertRegExp('/\<li\>bop\<\/li\>/', $this->messages->getFormatted('info'));
	}
	
	public function testgetFormattedSimpleClassOverride()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormatted('error')));
		$this->assertRegExp('/class\="bip"/', $this->messages->getFormatted('error', ['error' => 'bip']));
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
		
		$this->assertTrue(is_array($this->messages->get()));
		$this->assertEquals(2, count($this->messages->get()));
		$this->assertTrue(is_array($this->messages->get()['info']));
		$this->assertEquals(3, count($this->messages->get()['info']));
	}
	
	public function testGetAllFormattedMessages()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$output_should_be = '<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz</li></ul></div><div class="alert alert-info"><ul><li>bip</li><li>bap</li><li>bop</li></ul></div>';
		
		$this->assertTrue(is_string($this->messages->getFormatted()));
		$this->assertEquals($output_should_be, $this->messages->getFormatted());
	}
	
	public function testNestingget()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->get('error')));
		$this->assertTrue(is_array($this->messages->get('error')['baz']));
		$this->assertEquals('bip', $this->messages->get('error')['baz'][0]);
		$this->assertEquals('bap', $this->messages->get('error')['baz'][1]);
	}
	
	public function testNestingGetFomattedMessages()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormatted('error')));
		$this->assertEquals('<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz<ul><li>bip</li><li>bap</li><li>bop</li></ul></li></ul></div>', $this->messages->getFormatted('error'));
	}
}