<?php

declare(strict_types=1);

/**
 * Minimal SMTP sender for Hostinger (ssl://smtp.hostinger.com:465).
 */
function sendSmtpEmail(
    string $to,
    string $subject,
    string $body,
    string $fromEmail,
    string $fromName,
    string $replyTo
): bool {
    if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASS')) {
        return false;
    }

    if (SMTP_USER === '' || SMTP_PASS === '') {
        return false;
    }

    $host = SMTP_HOST;
    $port = defined('SMTP_PORT') ? (int) SMTP_PORT : 465;
    $timeout = 30;
    $remote = 'ssl://' . $host . ':' . $port;

    $socket = @stream_socket_client($remote, $errno, $errstr, $timeout);

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, $timeout);

    if (!smtpExpect($socket, [220])) {
        fclose($socket);
        return false;
    }

    $ehloHost = 'greentorqueev.com';

    if (!smtpCommand($socket, 'EHLO ' . $ehloHost, [250])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'AUTH LOGIN', [334])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, base64_encode(SMTP_USER), [334])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, base64_encode(SMTP_PASS), [235])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251])) {
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'DATA', [354])) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'From: ' . encodeMimeHeader($fromName) . ' <' . $fromEmail . '>',
        'To: <' . $to . '>',
        'Reply-To: <' . $replyTo . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Subject: ' . $encodedSubject,
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace(["\r\n", "\r"], "\n", $body);
    $message = str_replace("\n", "\r\n", $message);
    $message = preg_replace('/^\./m', '..', $message) ?? $message;

    fwrite($socket, $message . "\r\n.\r\n");

    if (!smtpExpect($socket, [250])) {
        fclose($socket);
        return false;
    }

    smtpCommand($socket, 'QUIT', [221]);
    fclose($socket);

    return true;
}

function encodeMimeHeader(string $value): string
{
    if (preg_match('/[^\x20-\x7E]/', $value)) {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    return $value;
}

function smtpCommand($socket, string $command, array $expectedCodes): bool
{
    fwrite($socket, $command . "\r\n");

    return smtpExpect($socket, $expectedCodes);
}

function smtpExpect($socket, array $expectedCodes): bool
{
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    if ($response === '') {
        return false;
    }

    $code = (int) substr($response, 0, 3);

    return in_array($code, $expectedCodes, true);
}
