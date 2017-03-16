<?php

namespace Techart\Mail\Transport;

/**
 * Отправляет сообщение с помощью функции mail
 *
 * @package Mail\Transport\PHPSender
 */
class PHPSender
{

    /**
     * Отправляет сообщение
     *
     * @param \Techart\Mail\Message\Message $message
     *
     * @return boolean
     */
    public function send(\Techart\Mail\Message\Message $message)
    {
        $encoder = \Techart\Mail\Serialize::Encoder();
        return mail(
            preg_replace('{^To:\s*}', '', $message->head['To']->encode()),
            preg_replace('{^Subject:\s*}', '', $message->head['Subject']->encode()),
            $encoder->to_string()->encode_body($message),
            $encoder->to_string()->encode_head(
                $message,
                array('To' => false, 'Subject' => false)
            )
        );
    }

}

