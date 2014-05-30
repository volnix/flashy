<?php namespace Volnix\Flashy\Tests;

use Volnix\Flashy\FormData;

class FormDataTests extends \PHPUnit_Framework_TestCase {

	private $form_data = null;

	public function __construct()
	{
		error_reporting(E_ALL);
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

	public function testConstructorSet()
	{
		unset($this->form_data);
		$this->form_data = new FormData(['foo' => 'bar']);
		$this->assertEquals('bar', $this->form_data->get('foo'));
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

	public function testGetFormDataDefaultValue()
	{
		$data = ['foo' => 'bar'];
		$this->form_data->set($data);

		$this->assertEquals('baz', $this->form_data->get('bar', 'baz'));
	}

	public function testSetFormValueMethodChaining()
	{
		$data = ['foo' => 'bar'];
		$value = $this->form_data->set($data)->get('foo');

		$this->assertEquals('bar', $value);
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

	public function testGetAll()
	{
		$data = ['foo' => 'bar', 'biz' => 'baz'];
		$this->form_data->set($data);

		$output = $this->form_data->get();

		$this->assertEquals('bar', $output['foo']);
		$this->assertEquals('baz', $output['biz']);
	}

	public function testStringEscape()
	{
		$data = "<script>alert('xss');</script>";
		$this->assertEquals(htmlspecialchars($data), $this->form_data->escape($data));
	}

	public function testArrayEscape()
	{
		$data = ['foo' => "<script>alert('xss');</script>"];
		$this->assertEquals(htmlspecialchars($data['foo']), $this->form_data->escape($data)['foo']);
	}

	public function testGetWithEscape()
	{
		$data = "<script>alert('xss');</script>";
		$this->assertEquals($data, $this->form_data->set('foo', $data)->get('foo'));
		$this->assertEquals(htmlspecialchars($data), $this->form_data->set('foo', $data)->get('foo', null, true));
	}
}