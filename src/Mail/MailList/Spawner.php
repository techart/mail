<?php

namespace Techart\Mail\MailList;

/**
 * Формирует персонализированные письма ввиде файлов
 *
 * @package Mail\List
 */
class Spawner
{
	protected $message;
	protected $list;
	protected $id;

	/**
	 * Конструктор
	 *
	 * @param \Techart\Mail\Message\Message $message
	 * @param array()              $list
	 */
	public function __construct(\Techart\Mail\Message\Message $message, $list)
	{
		$this->list = $list;
		$this->message = $message;
		$this->id = md5(time().rand(0,10000000));
	}

	/**
	 * Делает всю работу - формирует файлы с залоговками и письмом
	 *
	 */
	public function spawn()
	{
		$this->body()->heads();
		return $this->id;
	}

	/**
	 * Устанавливает или считывает идентификатор
	 *
	 * @param string $value
	 */
	public function id($value = null)
	{
		if ($value !== null) {
			$this->id = (string)$value;
			return $this;
		}
		return $this->id;
	}

	/**
	 * Формирует основную часть письма
	 *
	 */
	protected function body()
	{
		$messages_path = \Techart\Mail\MailList::option('root') . '/messages';
		\Techart\IO\FS::mkdir($messages_path);
		$path = sprintf('%s/%s.body', $messages_path, $this->id);
		\Techart\IO\FS::rm($path);
		$f = \Techart\IO\FS::File($path);
		$f->
			open('w')->
			write(\Techart\Core::with(new \Techart\Mail\MailList\Encoder())->encode($this->message))->
			close();
		$f->chmod(0664);

		return $this;
	}

	/**
	 * Формирует файлы с заголовками и параметрами
	 *
	 */
	protected function heads()
	{
		\Techart\IO\FS::mkdir(\Techart\Mail\MailList::option('root') . '/recipients');
		foreach ($this->list as $k => $v)
			$this->head($v, $k);
		return $this;
	}

	/**
	 * Формирует один фаил с заголовками и параметрами
	 *
	 * @param     $container
	 * @param int $index
	 */
	protected function head($container, $index)
	{
		$values = array();
		$headers = \Techart\Mail\Message::Head();
		foreach ($container as $k => $v) {
			if (array_search($k, \Techart\Mail\MailList::option('headers'), true) !== false) {
				$headers->field($k, $v);
			} else {
				$values[] = sprintf("-%s: %s", $k, \Techart\MIME::encode_qp($v, null));
			}
		}
		$path = sprintf('%s/%s.%06d', \Techart\Mail\MailList::option('root') . '/recipients', $this->id, $index);
		\Techart\IO\FS::rm($path);
		$f = \Techart\IO\FS::File($path);
		$f->
			open('w')->
			write($headers->encode() . (count($values) ? implode("\n", $values) . "\n" : ''))->
			close();
		$f->chmod(0664);
	}

}
