<?php
namespace TypeRocketPro\Utility\Mailers;

use TypeRocketPro\Utility\Log;
use TypeRocket\Utility\Mailers\MailDriver;

class LogMailDriver implements MailDriver
{
    /**
     * @param string|array $to
     * @param string $subject
     * @param string $message
     * @param string|array $headers
     * @param array $attachments
     *
     * @return bool
     */
    public function send($to, $subject, $message, $headers = '', $attachments = []): bool
    {
        return Log::info(json_encode([
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
            'attachments' => $attachments,
        ]));
    }
}