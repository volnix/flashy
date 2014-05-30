<?php namespace Volnix\Flashy;


class FormData {

	private $flash_data = [];

	/**
	 * Constructor
	 *
	 * You may override the generic session library with a custom one if you desire
	 *
	 * @access public
	 * @param $data array The data to initialize the form flash dump with
	 */
	public function __construct($data = [])
	{
		if (!empty($data)) {
			$this->set($data);
		}
	}

	/**
	 * Clear the flash dump
	 *
	 * @access public
	 * @return void
	 */
	public function clear()
	{
		$this->flash_data = [];
	}

	/**
	 * Get Flashes
	 *
	 * If key is not specified, this will return all flashes.  If so, then it will return just the key.  If a non-existent key is requested, it will use the default
	 *
	 * @param string $key The key to retrieve, leave blank to get all data
	 * @param string $default The default to use if the key isn't defined
	 * @param bool $escape Whether or not to escape the data
	 * @return array|string
	 */
	public function get($key = "", $default = "", $escape = false)
	{
		if (empty($key)) {
			return $escape ? $this->escape($this->flash_data) : $this->flash_data;
		} elseif (isset($this->flash_data[$key])) {
			return $escape ? $this->escape($this->flash_data[$key]) : $this->flash_data[$key];
		} else {
			return $escape ? $this->escape($default) : $default;
		}
	}

	/**
	 * Function to (recursively) escape data if desired
	 *
	 * @param string $data
	 * @return array|string
	 */
	public function escape($data = "")
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->escape($value);
			}
		} else {
			$data = htmlspecialchars($data);
		}

		return $data;
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
