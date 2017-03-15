<?php

namespace Techart\Mail\Serialize;

class Encoder
{
	protected $output = '';

	/**
	 * Устанавливает запись результата в поток
	 *
	 * @param \Techart\IO\Stream\AbstractStream $output
	 *
	 * @return \Techart\Mail\Serialize\Encoder
	 */
	public function to_stream(\Techart\IO\Stream\AbstractStream $output)
	{
		$this->output = $output;
		return $this;
	}

	/**
	 * Устанавливает запись результата в строку
	 *
	 * @param $output
	 *
	 * @return $this
	 */
	public function to_string($output = '')
	{
		$this->output = (string)$output;
		return $this;
	}

	/**
	 * Кодирует почтовое сообщение Mail.Message.Message
	 *
	 * @param \Techart\Mail\Message\Part $message
	 *
	 * @return mixed
	 */
	public function encode(\Techart\Mail\Message\Part $message)
	{
		$this->encode_head($message);
		$this->write();
		$this->encode_body($message);
		return $this->output;
	}

	/**
	 * Кодирует заголовок письма
	 *
	 * @param \Techart\Mail\Message\Part $message
	 * @param array             $fields
	 *
	 * @return mixed
	 */
	public function encode_head(\Techart\Mail\Message\Part $message, array $fields = array(true))
	{
		\Techart\Core\Strings::begin_binary();

		$include_by_default = isset($fields[0]) ? (boolean)$fields[0] : true;

		foreach ($message->head as $field) {
			$name = $field->name;

			$include_field = isset($fields[$name]) ?
				$fields[$name] :
				$include_by_default;

			if ($include_field) {
				$this->write($field->encode());
			}
		}

		\Techart\Core\Strings::end_binary();
		return $this->output;
	}

	/**
	 * Кодирует содержимое сообщения
	 *
	 * @param \Techart\Mail\Message\Part $msg
	 *
	 * @return mixed
	 */
	public function encode_body(\Techart\Mail\Message\Part $msg)
	{
		\Techart\Core\Strings::begin_binary();

		if ($msg instanceof \Techart\Mail\Message\Message && $msg->is_multipart()) {
			$boundary = $msg->head['Content-Type']['boundary'];
		}

		if (isset($boundary)) {
			if ($msg->preamble != '') {
				$this->write($msg->preamble);
			}

			foreach ($msg->body as $part) {
				$this->write("--$boundary");
				$this->encode($part);
			}
			$this->write("--$boundary--");

			if ($msg->epilogue != '') {
				$this->write($msg->epilogue);
			}
		} else {
			$body = ($msg->body instanceof \Techart\IO\FS\File || $msg->body instanceof \Techart\IO\Stream\ResourceStream) ?
				$msg->body->load() : $msg->body;
//      if ($msg->body instanceof \Techart\IO\FS\File) {
//        foreach (
//          $this->encoder_for($msg)->
//            from_stream($msg->body->open()) as $line)
//              $this->write($line);
//        $msg->body->close();
//      } elseif ($msg->body instanceof \Techart\IO\Stream\AbstractStream) {
//        foreach (
//          $this->encoder_for($msg)->from_stream($msg->body) as $line)
//            $this->write($line);
//      } else {
			$this->write($this->encoder_for($msg)->encode($body));
//      }
		}

		\Techart\Core\Strings::end_binary();
		return $this->output;
	}

	/**
	 * Возвращает MIME-кодировщик для сообщения $msg
	 *
	 * @param \Techart\Mail\Message\Part $msg
	 *
	 * @return \Techart\MIME\Encode\AbstractEncoder
	 */
	protected function encoder_for(\Techart\Mail\Message\Part $msg)
	{
		return \Techart\MIME\Encode::encoder(isset($msg->head['Content-Transfer-Encoding']) ?
				$msg->head['Content-Transfer-Encoding']->value : null
		);
	}

	/**
	 * Пишет результат кодирования
	 *
	 * @param string $string
	 *
	 * @return \Techart\Mail\Serialize\Encoder
	 */
	protected function write($string = '')
	{
		if (substr($string, -1) != \Techart\MIME::LINE_END) {
			$string .= \Techart\MIME::LINE_END;
		}
		($this->output instanceof \Techart\IO\Stream\AbstractStream) ?
			$this->output->write($string) :
			$this->output .= $string;
		return $this;
	}

}

