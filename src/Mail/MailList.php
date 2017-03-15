<?php

namespace Techart\Mail;

class MailList
{

	static protected $options = array(
		'root' => '.',
		'headers' => array('To', 'Subject', 'List-Unsubscribe'));
	const VERSION = '0.2.1';

	/**
	 * Инициализация
	 *
	 * @param array $options
	 */
	static public function initialize(array $options = array())
	{
		self::options($options);
	}

	/**
	 * устанавливает опции модуля
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	static public function options(array $options = array())
	{
		if (count($options)) {
			\Techart\Core\Arrays::update(self::$options, $options);
		}
		return self::$options;
	}

	/**
	 * Установка или чтение опции
	 *
	 * @param string $name
	 * @param        $value
	 *
	 * @return mixed
	 */
	static public function option($name, $value = null)
	{
		$prev = isset(self::$options[$name]) ? self::$options[$name] : null;
		if ($value !== null) {
			self::options(array($name => $value));
		}
		return $prev;
	}

	/**
	 * Фабричный метод, возвращает объект класса Mail.List.Spawner
	 *
	 * @param \Techart\Mail\Message\Message $message
	 * @param                      $list
	 *
	 * @return \Techart\Mail\MailList\Spawner
	 */
	static public function Spawner(\Techart\Mail\Message\Message $message, $list)
	{
		return new \Techart\Mail\MailList\Spawner($message, $list);
	}

}
