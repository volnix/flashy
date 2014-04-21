<?php namespace Volnix\Flashy;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use \InvalidArgumentException;

class FormData {

	public $session	= null;
	private $flash_data	= [];

	const SESSION_INDEX		= "_flashy_form_data";

	public function __construct(Symfony\Component\HttpFoundation\Session\SessionInterface $session = null)
	{
		$this->setSession($session);
	}

	public function setSession(Symfony\Component\HttpFoundation\Session\SessionInterface $session = null)
	{
		if (empty($session)) {
			$this->session = (new Session((new NativeSessionStorage), null, (new AutoExpireFlashBag)));
		} else {
			$this->session = $session;
		}

		$this->flash_data = $this->session->getFlashBag()->get(self::SESSION_INDEX);
	}
	/**
	 * Retrieve the key being asked for.  If no data is contained in that key, then return the default.
	 *
	 * @access public
	 * @param string $key (default: "")
	 * @param string $default (default: "")
	 * @return void
	 */
	public function get($key = "", $default = "")
	{
		// no key, so return all the flash data
		if (empty($key)) {
			return $this->flash_data;
		} elseif (isset($this->flash_data[$key])) {
			return $this->flash_data[$key];
		} else {
			return $default;
		}
	}

	/**
	 * Set form data.
	 *
	 * The key can be an array, in which case it will set the key as the form data, or it can be scalar in which case it will set data to the key
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $data (default: "")
	 * @return void
	 */
	public function set($key, $data = "")
	{
		if (is_array($key)) {
			$this->flash_data = $key;
		} elseif (is_string($data)) {
			$this->flash_data[$key] = $data;
		} else {
			throw new InvalidArgumentException(sprintf("Message must be an array or string.  '%s' given.", gettype($message)));
		}

		$this->session->getFlashBag()->set(self::SESSION_INDEX, $this->flash_data);
	}

	/**
	 * Clear our form data.  This is primarily used for unit testing.
	 *
	 * @access public
	 * @return void
	 */
	public function clear()
	{
		$this->set([]);
	}
}
