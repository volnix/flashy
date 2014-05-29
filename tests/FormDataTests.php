<?php namespace Volnix\Flashy\Tests;

use Symfony\Component\HttpFoundation\Session\Session;
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
		$this->form_data = new FormData;
	}

	public function tearDown()
	{
		unset($this->form_data);
	}

	public function testSetFormDataSetValueString()
	{
		$this->form_data->set('foo', 'bar');
		$this->assertEquals('bar', $this->form_data->get('foo'));
	}

	public function testSetFormDataSetValueArray()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		$this->assertEquals('bar', $this->form_data->get('foo'));
	}

	public function testSetFormDataSetValueNewSession()
	{
		unset($this->form_data);
		$this->form_data = new FormData(new Session);

		$this->form_data->set('foo', 'bar');
		$this->assertEquals('bar', $this->form_data->get('foo'));
	}

	public function testGetFormDataClearsValue()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		$this->assertEquals('bar', $this->form_data->get('foo'));
		$this->assertNotEquals('bar', $this->form_data->get('foo'));
	}

	public function testGetFormDataDefaultValue()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		$this->assertEquals('baz', $this->form_data->get('bar', 'baz'));
	}

	public function testSetFormValueMethodChaining()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data)->saveData();

		$this->assertEquals('bar', $this->form_data->get('foo'));
	}

	public function testClear()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		$this->assertEquals('bar', $this->form_data->get('foo'));

		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		$this->form_data->clear();
		$this->assertNotEquals('bar', $this->form_data->get('foo'));
	}

	public function testClearSession()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);
		$this->assertEquals('bar', $this->form_data->get('foo'));

		$data = ['foo' => 'bar'];
		$this->form_data->set($data)->saveData();
		$this->assertEquals('bar', (new Session)->get(FormData::SESSION_INDEX)['foo']);
		$this->form_data->clear(true);
		$this->assertNotEquals('bar', $this->form_data->get('foo'));
		$this->assertNotEquals('bar', (new Session)->get(FormData::SESSION_INDEX)['foo']);
	}

	public function testGetAll()
	{
		$data = ['foo' => 'bar', 'biz' => 'baz'];
		$this->form_data->set($data);

		$output = $this->form_data->get();

		$this->assertEquals('bar', $output['foo']);
		$this->assertEquals('baz', $output['biz']);
	}

	public function testSetFormDataSetValueMultiRequest()
	{
		// request starts
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		// request ends
		unset($this->form_data);

		// request restarts
		$this->form_data = new FormData;
		$this->assertEquals('bar', $this->form_data->get('foo'));

		// request ends
		unset($this->form_data);

		//request starts agains
		$this->form_data = new FormData;
		$this->assertNotEquals('bar', $this->form_data->get('foo'));
		$this->assertEquals("", $this->form_data->get('foo'));
	}
}