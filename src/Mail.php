<?php
 
namespace Techart;
 
class Mail
{
	static public function Message()
	{
		return new \Techart\Mail\Message\Message();
	}
	
	static public function Part()
	{
		return new \Techart\Mail\Message\Part();
	}

	/**
	 * Фабричный метод, возвращает объект класса Mail.Message.Serializer
	 *
	 * @param \Techart\IO\Stream\AbstractStream|null $stream
	 *
	 * @return \Techart\Mail\Serialize\Encoder
	 */
	static public function Encoder(\Techart\IO\Stream\AbstractStream $stream = null)
	{
		return new \Techart\Mail\Serialize\Encoder($stream);
	}

}
