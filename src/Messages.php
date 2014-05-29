<?php namespace Volnix\Flashy;

use Symfony\Component\HttpFoundation\Session\Session;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Messages {

	private $session    = null;
	private $messages   = [];

	const SESSION_INDEX = "_flashy_messages_65df6aa59e";

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
			$this->messages = $this->session->get(self::SESSION_INDEX);
			$this->session->remove(self::SESSION_INDEX);
		} else {
			$this->messages =  [];
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
		$this->session->set(self::SESSION_INDEX, $this->messages);
	}

	/**
	 * Clear the message dump
	 *
	 * @access public
	 * @param bool $clear_session Clear the session too
	 * @return void
	 */
	public function clear($clear_session = false)
	{
		$this->messages = [];

		if ($clear_session === true) {
			$this->session->remove(self::SESSION_INDEX);
		}
	}

	/**
	 * Return a ul of the messages for a given type.  Optionally pass in a class override array if you want to use custom classes.  Otherwise will default to bootstrap 3 classes.
	 *
	 * @access public
	 * @param string $type (default: "")
	 * @param mixed $classes (default: [])
	 * @return string
	 */
	public function getFormatted($type = "", $classes = [])
	{
		$message_class = !empty($classes[$type]) ? $classes[$type] : sprintf('alert alert-%s', ($type == 'error' ? 'danger' : htmlspecialchars($type)));

		if (!empty($type) && is_array($this->messages[$type]) && count($this->messages[$type]) > 0) {

			$message_content = sprintf('<div class="%s">', $message_class);
			$message_content .= $this->ul($this->get($type));
			$message_content .= '</div>';
			return $message_content;

		} elseif (empty($type) && is_array($this->messages) && count($this->messages) > 0) {

			// iterate through the message types, calling this function recursively
			$messages = "";
			foreach (array_keys($this->messages) as $msg_type) {
				$messages .= $this->getFormatted($msg_type);
			}
			return $messages;

		} else {
			return "";
		}
	}

	/**
	 * Get messages
	 *
	 * If type is not specified, this will return all messages.  If so, then it will return just the type.  If a non-existent message type is requested, an empty array will be returned.
	 *
	 * @access public
	 * @param mixed $type (default: NULL)
	 * @return mixed
	 */
	public function get($type = NULL)
	{
		if (empty($type)) {
			$messages = $this->messages;
			$this->clear();
			return $messages;
		} elseif (empty($this->messages[$type])) {
			return [];
		} else {
			$messages = $this->messages[$type];
			unset($this->messages[$type]);
			return $messages;
		}
	}

	/**
	 * Generic set function
	 *
	 * @param string $type
	 * @param string $message
	 * @return $this Used for method chaining
	 * @throws \InvalidArgumentException
	 */
	public function set($type = "", $message = "")
	{
		if (is_array($message)) {
			$this->messages[$type] = $message;
		} elseif (is_string($message)) {
			$this->messages[$type][] = $message;
		} else {
			throw new InvalidArgumentException(sprintf("Message must be an array or string.  '%s' given.", gettype($message)));
		}

		return $this;
	}

	/**
	 * Allow setting as array where [type => messages].
	 *
	 * @access public
	 * @param mixed $data (default: [])
	 * @return $this Used for method chaining
	 */
	public function setAsArray($data = [])
	{
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $type => $message) {
				$this->set($type, $message);
			}
		}

		return $this;
	}

	/**
	 * Call magic method is merely a way to access setMessages.
	 *
	 * @access public
	 * @param string $type (default: "")
	 * @param string $message (default: "")
	 * @return void
	 */
	public function __call($type = "", $message = "")
	{
		return $this->set($type, $message[0]);
	}

	/**
	 * Create an HTML unordered list of data
	 *
	 * @param array $data
	 * @return string
	 */
	private function ul($data = [])
	{
		$ul = '<ul>';
		foreach ($data as $header => $message) {
			if (is_string($message)) {
				// simple string, so stick in tags and rock n roll
				$ul .= sprintf('<li>%s</li>', $message);
			} elseif (is_array($message)) {
				// array, so call recursively
				$ul .= sprintf('<li>%s%s</li>', $header, $this->ul($message));
			}
		}
		$ul .= '</ul>';
		return $ul;
	}
}
