<?php namespace Volnix\Flashy;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FormData {

	private $session    = null;
	private $flash_data = [];

	const SESSION_INDEX		= "_flashy_form_65df6aa59e";

	/**
	 * Constructor
	 *
	 * You may override the generic session library with a custom one if you desire
	 *
	 * @access public
	 * @param SessionInterface $session A custom session object adhering to Symfony\Component\HttpFoundation\Session\SessionInterface
	 */
	public function __construct(SessionInterface $session = null)
	{
		if (!empty($session)) {
			$this->session = $session;
		} else {
			$this->session = new Session;
		}

		if ($this->session->has(self::SESSION_INDEX)) {
			$this->flash_data = $this->session->get(self::SESSION_INDEX);
			$this->session->remove(self::SESSION_INDEX);
		} else {
			$this->flash_data =  [];
		}
	}

	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct()
	{
		$this->saveData();
	}

	/**
	 * Save the data to session
	 *
	 * @access public
	 * @return void
	 */
	public function saveData()
	{
		$this->session->remove(self::SESSION_INDEX);
		$this->session->set(self::SESSION_INDEX, $this->flash_data);
	}

	/**
	 * Clear the flash dump
	 *
	 * @access public
	 * @param bool $clear_session Clear the session too
	 * @return void
	 */
	public function clear($clear_session = false)
	{
		$this->flash_data = [];

		if ($clear_session === true) {
			$this->session->remove(self::SESSION_INDEX);
		}
	}

	/**
	 * Get Flashes
	 *
	 * If key is not specified, this will return all flashes.  If so, then it will return just the key.  If a non-existent key is requested, it will use the default
	 *
	 * @param string $key
	 * @param string $default
	 * @return array|string
	 */
	public function get($key = "", $default = "")
	{
		if (empty($key)) {
			$flashes = $this->flash_data;
			$this->clear();
			return $flashes;
		} elseif (isset($this->flash_data[$key])) {
			$flash = $this->flash_data[$key];
			unset($this->flash_data[$key]);
			return $flash;
		} else {
			return $default;
		}
	}

	/**
	 * Set form data.
	 *
	 * The key can be an array, in which case it will set the key as the form data, or it can be scalar in which case it will set data to the key
	 *
	 * @param $key
	 * @param string $data
	 * @return $this Useful for method chaining
	 */
	public function set($key, $data = "")
	{
		if (is_array($key)) {
			$this->flash_data = $key;
		} else {
			$this->flash_data[$key] = $data;
		}

		return $this;
	}
}
