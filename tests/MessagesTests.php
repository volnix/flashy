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
		$this->setSession();
		$this->messages = new Messages($this->session);
	}
	
	public function tearDown()
	{
		// wipe our form data so we have a fresh object to work with
		$this->messages->emptyMessages();
	}
	
	public function testGetMessagesSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages('error')));
		$this->assertEquals('foo', $this->messages->getMessages('error')[0]);
		$this->assertEquals('bar', $this->messages->getMessages('error')[1]);
		$this->assertEquals('baz', $this->messages->getMessages('error')[2]);
	}
	
	public function testGetFormattedMessagesSimple()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('error')));
		$this->assertRegExp('/alert alert\-danger/', $this->messages->getFormattedMessages('error'));
	}
	
	public function testGetMessagesComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages('error')));
		$this->assertEquals('foo', $this->messages->getMessages('error')[0]);
		
		$this->assertTrue(is_array($this->messages->getMessages('info')));
		$this->assertEquals('bip', $this->messages->getMessages('info')[0]);
	}
	
	public function testGetFormattedMessagesComplex()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('error')));
		$this->assertRegExp('/alert alert\-danger/', $this->messages->getFormattedMessages('error'));
		$this->assertRegExp('/\<li\>foo\<\/li\>/', $this->messages->getFormattedMessages('error'));
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('info')));
		$this->assertRegExp('/alert alert\-info/', $this->messages->getFormattedMessages('info'));
		$this->assertRegExp('/\<li\>bop\<\/li\>/', $this->messages->getFormattedMessages('info'));
	}
	
	public function testGetFormattedMessagesSimpleClassOverride()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('error')));
		$this->assertRegExp('/class\="bip"/', $this->messages->getFormattedMessages('error', ['error' => 'bip']));
	}
	
	public function testGetNonExistentMessageType()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages('bad_type')));
		$this->assertEquals(0, count($this->messages->getMessages('bad_type')));
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('bip')));
		$this->assertEquals("", $this->messages->getFormattedMessages('bip'));
	}
	
	public function testGetAllMessages()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages()));
		$this->assertEquals(2, count($this->messages->getMessages()));
		$this->assertTrue(is_array($this->messages->getMessages()['info']));
		$this->assertEquals(3, count($this->messages->getMessages()['info']));
	}
	
	public function testGetAllFormattedMessages()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$messages = ['bip', 'bap', 'bop'];
		$this->messages->info($messages);
		
		$output_should_be = '<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz</li></ul></div><div class="alert alert-info"><ul><li>bip</li><li>bap</li><li>bop</li></ul></div>';
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages()));
		$this->assertEquals($output_should_be, $this->messages->getFormattedMessages());
	}
	
	public function testNestingGetMessages()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages('error')));
		$this->assertTrue(is_array($this->messages->getMessages('error')['baz']));
		$this->assertEquals('bip', $this->messages->getMessages('error')['baz'][0]);
		$this->assertEquals('bap', $this->messages->getMessages('error')['baz'][1]);
	}
	
	public function testNestingGetFomattedMessages()
	{
		$messages = ['foo', 'bar', 'baz' => ['bip', 'bap', 'bop']];
		$this->messages->error($messages);
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('error')));
		$this->assertEquals('<div class="alert alert-danger"><ul><li>foo</li><li>bar</li><li>baz<ul><li>bip</li><li>bap</li><li>bop</li></ul></li></ul></div>', $this->messages->getFormattedMessages('error'));
	}
	
	private function setSession($sess_id = NULL)
	{
		$this->session = null; unset($this->session);
		$this->session = new Session(new MockFileSessionStorage(), null, new AutoExpireFlashBag());
		$this->session->setId($sess_id);
	}
}