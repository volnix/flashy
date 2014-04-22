<?php namespace Volnix\Flashy;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use \InvalidArgumentException;

class Messages {

	public $session	= null;
	private $messages = [];

	const SESSION_INDEX		= "_flashy_messages";

	/**
	 * Constructor.
	 *
	 * @param SessionInterface $session
	 */
	public function __construct(SessionInterface $session = null)
	{
		$this->setSession($session);
	}

	/**
	 * Configure our session.
	 *
	 * @param SessionInterface $session
	 */
	public function setSession(SessionInterface $session = null)
	{
		if (empty($session)) {
			$this->session = (new Session((new NativeSessionStorage), null, (new AutoExpireFlashBag)));
		} else {
			$this->session = $session;
		}

		$this->messages = $this->session->getFlashBag()->get(self::SESSION_INDEX);
	}

	/**
	 * Return an ul of the messages for a given type.  Optionally pass in a class override array if you want to use custom classes.  Otherwise will default to bootstrap 3 classes.
	 *
	 * @access public
	 * @param string $type (default: "")
	 * @param mixed $class (default: [])
	 * @return void
	 */
	public function getFormatted($type = "", $classes = [])
	{
		$message_class = !empty($classes[$type]) ? $classes[$type] : sprintf('alert alert-%s', ($type == 'error' ? 'danger' : htmlspecialchars($type)));

		if (!empty($type) && is_array($this->get($type)) && count($this->get($type)) > 0) {

			$message_content = sprintf('<div class="%s">', $message_class);
			$message_content .= $this->ul($this->get($type));
			$message_content .= '</div>';
			return $message_content;

		} elseif (empty($type) && is_array($this->get()) && count($this->get()) > 0) {

			// iterate through the message types, calling this function recursively
			$messages = "";
			foreach (array_keys($this->get()) as $msg_type) {
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
	 * @return void
	 */
	public function get($type = NULL)
	{
		if (empty($type)) {
			return $this->messages;
		} elseif (empty($this->messages[$type]) || !is_array($this->messages[$type])) {
			return [];
		} else {
			return $this->messages[$type];
		}
	}

	/**
	 * Generic setter function.
	 *
	 * @access private
	 * @param string $type (default: "")
	 * @param string $message (default: "")
	 * @return void
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

		$this->session->getFlashBag()->set(self::SESSION_INDEX, $this->messages);
	}

	/**
	 * Allow setting as array where [type => messages].
	 *
	 * @access public
	 * @param mixed $data (default: [])
	 * @return void
	 */
	public function setAsArray($data = [])
	{
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $type => $message) {
				$this->set($type, $message);
			}
		}
	}

	/**
	 * Empty our form data.  This is primarily used for unit testing.
	 *
	 * @access public
	 * @return void
	 */
	public function clear()
	{
		$this->messages = [];
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
		$this->set($type, $message[0]);
	}


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
