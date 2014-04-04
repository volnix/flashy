<?php namespace Volnix\Flashy\Tests;

use Volnix\Flashy\FormData;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;

class FormDataTests extends \PHPUnit_Framework_TestCase {
	
	private $form_data = null;
	private $save_path = "";
	private $session = null;
	
	public function __construct()
	{
		$this->resetSession();
		$this->form_data = new FormData;
		$this->form_data->setSession($this->session);
	}
	
	public function tearDown()
	{
		// wipe our form data so we have a fresh object to work with
		$this->form_data->clear();
	}
	
	public function testSetFormDataConstructor()
	{
		$data = ['foo' => 'bar'];
		$this->form_data = new FormData($data);
		$this->assertEquals('bar', $this->form_data->get('foo'));
	}
	
	public function testSetFormDataSetValue()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		
		$this->assertEquals('bar', $this->form_data->get('foo'));
	}
	
	public function testSetFormDataSetValueMultiRequest()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		
		// kill our session, then rebuild it and assign our old session ID to it to simulate an http request lifecycle
		$sess_id = $this->form_data->session->getId();
		$this->resetSession($sess_id);
		$this->form_data->setSession($this->session);
		$this->assertEquals('bar', $this->form_data->get('foo'));
		
		// kill our session again without a set.  this should not have 'bar' as the value this time
		$sess_id = $this->form_data->session->getId();
		$this->resetSession($sess_id);
		$this->form_data->setSession($this->session);
		
		$this->assertNotEquals('bar', $this->form_data->get('foo'));
		$this->assertEquals("", $this->form_data->get('foo'));
	}
	
	private function resetSession($sess_id = NULL)
	{
		$this->session = null; unset($this->session);
		$this->session = new Session(new MockFileSessionStorage(), null, new AutoExpireFlashBag());
		$this->session->setId($sess_id);
	}
}