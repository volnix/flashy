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
		$this->assertRegExp('/alert alert\-error/', $this->messages->getFormattedMessages('error'));
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
		$this->assertRegExp('/alert alert\-error/', $this->messages->getFormattedMessages('error'));
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
	
	public function getNonExistentMessageType()
	{
		$messages = ['foo', 'bar', 'baz'];
		$this->messages->error($messages);
		
		$this->assertTrue(is_array($this->messages->getMessages('bad_type')));
		$this->assertTrue((count($this->messages->getMessages('bad_type')) == 0));
		
		$this->assertTrue(is_string($this->messages->getFormattedMessages('bip')));
		$this->assertEquals("", $this->messages->getFormattedMessages('bip'));
	}
	
	private function setSession($sess_id = NULL)
	{
		$this->session = null; unset($this->session);
		$this->session = new Session(new MockFileSessionStorage(), null, new AutoExpireFlashBag());
		$this->session->setId($sess_id);
	}
}