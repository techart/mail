<?php

namespace Techart\Mail\Serialize;

class Decoder
{

	protected $input;
	/** @noinspection PhpUndefinedClassInspection */

	/**
	 * Устанвливает поток из которого береться сообщение
	 *
	 * @param \Techart\IO\Stream\AbstractStream $stream
	 *
	 * @return \Techart\Mail\Serialize\Decoder
	 */
	public function from(\Techart\IO\Stream\AbstractStream $stream)
	{
		$this->input = $stream;
		return $this;
	}

	/**
	 * Декодирует сообщение
	 *
	 * @return \Techart\Mail\Message\Message
	 */
	public function decode()
	{
		\Techart\Core\Strings::begin_binary();
		$result = ($head = $this->decode_head()) ?
			$this->decode_part(\Techart\Mail::Message()->head($head)) :
			null;
		\Techart\Core\Strings::end_binary();
		return $result;
	}

	/**
	 * Декодирует часть сообщения
	 *
	 * @param \Techart\Mail\Message\Part $parent
	 *
	 * @return \Techart\Mail\Message\Part
	 */
	protected function decode_part(\Techart\Mail\Message\Part $parent)
	{
		if ($parent->is_multipart()) {

			$parent->preamble($this->skip_to_boundary($parent));

			while (true) {
				if (!$head = $this->decode_head()) {
					break;
				}

				if (\Techart\Core\Regexps::match('{^[Mm]ultipart}', $head['Content-Type']->value)) {

					$parent->part($this->decode_part(\Techart\Mail::Message()->head($head)));

					$parent->epilogue($this->skip_to_boundary($parent));

				} else {
					$decoder = \Techart\MIME\Decode::decoder($head['Content-Transfer-Encoding']->value)->
						from_stream($this->input)->
						with_boundary($parent->head['Content-Type']['boundary']);

					$parent->part(\Techart\Mail::Part()->
							head($head)->
							body($this->is_text_content_type($head['Content-Type']->value) ?
									$decoder->to_string() :
									$decoder->to_temporary_stream()
							)
					);

					if ($decoder->is_last_part()) {
						break;
					}
				}
			}
		} else {
			$decoder = \Techart\MIME\Decode::decoder($parent->head['Content-Transfer-Encoding']->value)->
				from_stream($this->input);

			$parent->body($this->is_text_content_type($parent->head['Content-Type']->value) ?
					$decoder->to_string() :
					$decoder->to_temporary_stream()
			);
		}
		return $parent;
	}

	/**
	 * Определяет является ли тип текстовым
	 *
	 * @param string $type
	 *
	 * @return boolean
	 */
	protected function is_text_content_type($type)
	{
		return \Techart\Core\Regexps::match('{^(text|message)/}', (string)$type);
	}

	/**
	 * Декодирует заголовок сообщения
	 *
	 * @return \Techart\Mail\Message\Head
	 */
	protected function decode_head()
	{
		$data = '';

		while (($line = $this->input->read_line()) &&
			!\Techart\Core\Regexps::match("{^\n\r?$}", $line))
			$data .= $line;

		return $this->input->eof() ? null : \Techart\Mail\Message\Head::from_string($data);
	}

	/**
	 * Пролистывает сообщение до следующей границы
	 *
	 * @param string $boundary
	 *
	 * @return string
	 */
	protected function skip_to_boundary(\Techart\Mail\Message\Part $part)
	{
		$text = '';
		while (($line = $this->input->read_line()) &&
			!\Techart\Core\Regexps::match("{^--" . $part->head['Content-Type']['boundary'] . "(?:--)?\n\r?$}", $line))
			$text .= $line;
		return $text;
	}

}

