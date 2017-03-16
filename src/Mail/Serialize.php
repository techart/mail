<?php

namespace Techart\Mail;

/**
 * Mail.Serialize
 *
 * Модуль для кодирования и декодирования письма
 *
 * @package Mail\Serialize
 * @version 0.2.3
 */
class Serialize
{
    /**
     * Фабричный метод, возвращает объект класаа Mail.Serialize.Encoder
     *
     * @return \Techart\Mail\Serialize\Encoder
     */
    static public function Encoder()
    {
        return new \Techart\Mail\Serialize\Encoder();
    }

    /**
     * Фабричный метод, возвращает объект класаа Mail.Serialize.Decoder
     *
     * @return \Techart\Mail\Serialize\Decoder
     */
    static public function Decoder()
    {
        return new \Techart\Mail\Serialize\Decoder();
    }

}
