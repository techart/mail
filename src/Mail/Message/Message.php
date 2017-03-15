<?php

namespace Techart\Mail\Message;

/**
 * Почтовое письмо
 *
 * @package Mail\Message
 *
 * @method $this from($from)
 * @method $this to($to)
 * @method $this subject($subject)
 */
class Message
	extends Part
	implements \IteratorAggregate
{

	protected $preamble = '';
	protected $epilogue = '';

	/**
	 * конструктор
	 *
	 * @param boolean $nested
	 */
	public function __construct($nested = false)
	{
		parent::__construct();
		if (!$nested) {
			$this->head['MIME-Version'] = '1.0';
			$this->date(new \DateTime());
		}
	}

	public function send($transport = null)
	{
		return \Techart\Mail\Message::send($this, $transport);
	}

	/**
	 * Устанавливает заголовок письма в multipart с указанным типом и границей
	 *
	 * @param string $type
	 * @param string $boundary
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function multipart($type = 'mixed', $boundary = null)
	{
		$this->body = new \ArrayObject();

		$this->head['Content-Type'] = array(
			'Multipart/' . ucfirst(strtolower($type)),
			'boundary' => ($boundary ? $boundary : \Techart\MIME::boundary()));

		return $this;
	}

	/**
	 * Устанавливает заголовок письма в multipart/mixed
	 *
	 * @param string $boundary
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function multipart_mixed($boundary = null)
	{
		return $this->multipart('mixed', $boundary);
	}

	/**
	 * Устанавливает заголовок письма в multipart/alternative
	 *
	 * @param string $boundary
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function multipart_alternative($boundary = null)
	{
		return $this->multipart('alternative', $boundary);
	}

	/**
	 * Устанавливает заголовок письма в multipart/related
	 *
	 * @param string $boundary
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function multipart_related($boundary = null)
	{
		return $this->multipart('related', $boundary);
	}

	/**
	 * Устанавливает дату в заголовке
	 *
	 * @param  $date
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function date($date)
	{
		$this->head['Date'] = ($date instanceof \DateTime) ? $date->format(\DateTime::RFC1123) : (string)$date;
		return $this;
	}

	/**
	 * Добавляет к письму часть
	 *
	 * @param \Techart\Mail\Message\Part $part
	 *
	 * @return \Techart\Mail\Message\Message
	 * @throws \Techart\Mail\Message\Exception
	 */
	public function part(Part $part)
	{
		if (!$this->body instanceof \ArrayObject) {
			$this->body = new \ArrayObject();
		}

		if ($this->is_multipart()) {
			$this->body->append($part);
		} else {
			throw new Exception('Not multipart message');
		}
		return $this;
	}

	/**
	 * Добавляет к письму текстовую часть
	 *
	 * @param string $text
	 * @param        $content_type
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function text_part($text, $content_type = null)
	{
		return $this->part(\Techart\Mail\Message::Part()->text($text, $content_type));
	}

	/**
	 * Добавляте к письму html-часть
	 *
	 * @param string $text
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function html_part($text)
	{
		return $this->part(\Techart\Mail\Message::Part()->html($text));
	}

	/**
	 * Добавляет к письму attach фаил
	 *
	 * @param        $file
	 * @param string $name
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function file_part($file, $name = '')
	{
		return $this->part(\Techart\Mail\Message::Part()->file($file, $name));
	}

	/**
	 * Добавляет к письму преабулу
	 *
	 * @param string $text
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function preamble($text)
	{
		$this->preamble = (string)$text;
		return $this;
	}

	/**
	 * Добавляет к письму эпилог
	 *
	 * @param string $text
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function epilogue($text)
	{
		$this->epilogue = (string)$text;
		return $this;
	}

	/**
	 * Возвращает итератор по вложенным частям письма
	 *
	 * @return \Iterator
	 */
	public function getIterator()
	{
		return $this->is_multipart() ?
			$this->body->getIterator() :
			new \ArrayIterator($this->body);
	}

	/**
	 * Проверяет имеет ли письмо вложения
	 *
	 * @return boolean
	 */
	public function is_multipart()
	{
		return \Techart\Core\Strings::starts_with(
			\Techart\Core\Strings::downcase($this->head['Content-Type']->value), 'multipart'
		);
	}

	/**
	 * Доступ на чтение к свойствам объекта
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get($property)
	{
		switch ($property) {
			case 'preamble':
			case 'epilogue':
				return $this->$property;
			default:
				return parent::__get($property);
		}
	}

	/**
	 * Доступ на запись к свойствам объекта
	 *
	 * @param string $property
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function __set($property, $value)
	{
		switch ($property) {
			case 'preamble':
			case 'epilogue':
				$this->$property = (string)$value;
				break;
			default:
				return parent::__set($property, $value);
		}
		return $this;
	}

	/**
	 * Проверяет установленно ли свойство объекта
	 *
	 * @param string $property
	 *
	 * @return boolean
	 */
	public function __isset($property)
	{
		switch ($property) {
			case 'preamble':
			case 'epilogue':
				return true;
			default:
				return parent::__isset($property);
		}
	}

	/**
	 * Выбрасывает исключение Core.UndestroyablePropertyException
	 *
	 * @param string $property
	 *
	 * @throws \Techart\Core\NotImplementedException
	 * @throws \Techart\Core\UndestroyablePropertyException
	 */
	public function __unset($property)
	{
		switch ($property) {
			case 'preamble':
			case 'epilogue':
				throw new \Techart\Core\UndestroyablePropertyException($property);
			default:
				parent::__unset($property);
		}
	}

}

