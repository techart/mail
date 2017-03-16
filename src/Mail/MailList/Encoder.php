<?php

namespace Techart\Mail\MailList;

/**
 * Кодировщич писема для массовой рассылки
 *
 * @package Mail\List
 */
class Encoder extends \Techart\Mail\Serialize\Encoder
{
    /**
     * Возвращает кодировщик для письма $msg
     *
     * @param \Techart\Mail\Message\Part $msg
     */
    protected function encoder_for(\Techart\Mail\Message\Part $msg)
    {
        return ($msg->head['Content-Transfer-Encoding']->value == \Techart\MIME::ENCODING_QP) ?
            parent::encoder_for($msg)->line_length(null) : parent::encoder_for($msg);
    }
}

