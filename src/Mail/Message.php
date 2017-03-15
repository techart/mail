<?php

namespace Techart\Mail;

class Message
{

	protected static $options = array(
		'transport' => 'php',
		'transport_classes' => array(
			'sendmail' => 'Mail.Transport.Sendmail.Sender',
			'php' => 'Mail.Transport.PHP.Sender',
		)
	);

	public static function initialize($options = array())
	{
		self::options($options);
	}

	public static function options($options = array())
	{
		self::$options = array_replace_recursive(self::$options, $options);
		return self::$options;
	}

	public static function option($name)
	{
		return self::$options[$name];
	}

	public static function transport($name = null)
	{
		if (is_null($name)) {
			$name = self::option('transport');
		}
		$classes = self::option('transport_classes');
		if (!isset($classes[$name])) {
			return null;
		}
		$class = $classes[$name];
		\Techart\Core::autoload($class);
		return \Techart\Core::make($class);
	}

	public static function send($msg, $transport = null)
	{
		$transport = self::transport($transport);
		if ($transport) {
			$transport->send($msg);
		}
	}

	/**
	 * фабричный метод, возвращает объект класса Mail.Message.Field
	 *
	 * @param string $name
	 * @param string $body
	 *
	 * @return \Techart\Mail\Message\Field
	 */
	static public function Field($name, $body)
	{
		return new \Techart\Mail\Message\Field($name, $body);
	}

	/**
	 * фабричный метод, возвращает объект класса Mail.Message.Head
	 *
	 * @return \Techart\Mail\Message\Head
	 */
	static public function Head()
	{
		return new \Techart\Mail\Message\Head();
	}

	/**
	 * фабричный метод, возвращает объект класса Mail.Message.Part
	 *
	 * @return \Techart\Mail\Message\Part
	 */
	static public function Part()
	{
		return new \Techart\Mail\Message\Part();
	}

	/**
	 * фабричный метод, возвращает объект класса Mail.Message.Message
	 *
	 * @param boolean $nested
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	static public function Message($nested = false)
	{
		return new \Techart\Mail\Message\Message($nested);
	}

}
