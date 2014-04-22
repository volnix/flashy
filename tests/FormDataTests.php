<?php namespace Volnix\Flashy\Tests;

use Volnix\Flashy\FormData;

class FormDataTests extends \PHPUnit_Framework_TestCase {

	private $form_data = null;

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
		$this->form_data = null; unset($this->form_data);
		$this->form_data = new FormData();
	}

	public function tearDown()
	{
		// wipe our form data so we have a fresh object to work with
		$this->form_data->clear();
	}

	public function testSetFormDataSetValue()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		$this->assertEquals('bar', $this->form_data->get('foo'));
	}

	public function testSetFormDataSetValueMultiRequest()
	{
		$this->form_data = null; unset($this->form_data);
		$this->form_data = new FormData();

		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		$this->assertEquals('bar', $this->form_data->get('foo'));

		// kill our session, then rebuild it and assign our old session ID to it to simulate an http request lifecycle
		$this->form_data = null; unset($this->form_data);
		$this->form_data = new FormData();

		$this->assertEquals('bar', $this->form_data->get('foo'));

		// kill our session again without a set.  this should not have 'bar' as the value this time
		$this->form_data = null; unset($this->form_data);
		$this->form_data = new FormData();

		$this->assertNotEquals('bar', $this->form_data->get('foo'));
		$this->assertEquals("", $this->form_data->get('foo'));
	}

	public function testCanInitWithSession()
	{
		$class = new FormData(new \Symfony\Component\HttpFoundation\Session\Session());
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\Session\SessionInterface', $class->session);
	}
}