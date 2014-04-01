<?php namespace Volnix\Flashy;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use \InvalidArgumentException;

class Messages {
	
	public $session	= null;
	private $flash	= null;
	
	const SESSION_INDEX		= "_flashy_messages";
	
	public function __construct($session = null)
	{
		$this->setSession($session);
	}
	
	public function __destruct()
	{
		$this->saveMessages();
	}
	
	/**
	 * Return an ul of the messages for a given type.  Optionally pass in a class override array if you want to use custom classes.  Otherwise will default to bootstrap 3 classes.
	 * 
	 * @access public
	 * @param string $type (default: "")
	 * @param mixed $class (default: [])
	 * @return void
	 */
	public function getFormattedMessages($type = "", $classes = [])
	{
		$message_class = !empty($classes[$type]) ? $classes[$type] : sprintf('alert alert-%s', ($type == 'error' ? 'danger' : htmlspecialchars($type)));
		
		if (!empty($type) && is_array($this->getMessages($type)) && count($this->getMessages($type)) > 0) {
			
			$message_content = sprintf('<div class="%s">', $message_class);
			$message_content .= $this->makeUl($this->getMessages($type));
			$message_content .= '</div>';
			return $message_content;
			
		} elseif (empty($type) && is_array($this->getMessages()) && count($this->getMessages()) > 0) {
			
			// iterate through the message types, calling this function recursively
			$messages = "";
			foreach (array_keys($this->getMessages()) as $msg_type) {
				$messages .= $this->getFormattedMessages($msg_type);
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
	public function getMessages($type = NULL)
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
	 * Set a new session to our form data object.  This is primarily used for unit testing.
	 * 
	 * @access public
	 * @param SessionInterface $session (default: null)
	 * @return void
	 */
	public function setSession(SessionInterface $session = null)
	{
		$this->session = null; unset($this->session);
		
		if (isset($session_storage) && !$session_storage instanceof SessionInterface) {
			throw new InvalidArgumentException(sprintf("You must pass in an instance of SessionInterface.  You gave me a '%s'.", gettype($session_storage)));
		} elseif (isset($session_storage)) {
			$this->session = $session;
		} else {
			$this->session = (new Session((new NativeSessionStorage), null, (new AutoExpireFlashBag)));
		}
		
		$this->flash_data = $this->session->getFlashBag()->get(self::SESSION_INDEX);
	}
	
	/**
	 * Empty our form data.  This is primarily used for unit testing.
	 * 
	 * @access public
	 * @return void
	 */
	public function emptyMessages()
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
		$this->setMessage($type, $message);
	}
	
	/**
	 * Write the form data to the session.
	 * 
	 * @access public
	 * @return void
	 */
	private function saveMessages()
	{
		$this->session->getFlashBag()->set(self::SESSION_INDEX, $this->messages);
	}
	
	
	private function makeUl($data = [])
	{
		$ul = '<ul>';
		foreach ($data as $header => $message) {
			if (is_string($message)) {
				// simple string, so stick in tags and rock n roll
				$ul .= sprintf('<li>%s</li>', $message);
			} elseif (is_array($message)) {
				// array, so call recursively
				$ul .= sprintf('<li>%s%s</li>', $header, $this->makeUl($message));
			}
		}
		$ul .= '</ul>';
		return $ul;
	}
	
	/**
	 * Generic setMessage function.
	 * 
	 * @access private
	 * @param string $type (default: "")
	 * @param string $message (default: "")
	 * @return void
	 */
	private function setMessage($type = "", $message = "")
	{
		if (is_array($message[0])) {
			$this->messages[$type] = $message[0];
		} elseif (is_string($message[0])) {
			$this->messages[$type][] = $message[0];
		} else {
			throw new InvalidArgumentException(sprintf("Message must be an array or string.  '%s' given.", gettype($message[0])));
		}
	}
}