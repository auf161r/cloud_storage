<?php

namespace app\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class MailService
{
    /**
     * Отправить ссылку для сброса пароля на email пользователя
     * 
     * @param string $email Email получателя
     * @param string $token Токен для сброса пароля
     * @return bool true если письмо отправлено успешно, false в случае ошибки
     */
    public function sendResetLink(string $email, string $token): bool
    {
        try {
            $config = parse_ini_file(__DIR__ . '/../../.env');
            $resetLink = $config['FRONTEND_URL'] . '/reset-password?token=' . $token;

            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $config['MAIL_HOST'];
            $mail->Port = $config['MAIL_PORT'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['MAIL_USERNAME'];
            $mail->Password = $config['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->setFrom($config['MAIL_FROM'], $config['MAIL_FROM_NAME']);
            $mail->addAddress($email);
            $mail->Subject = 'Reset password';
            $mail->CharSet = 'UTF-8';
            $mail->Body = "Для сброса пароля перейдите по ссылке: <a href='$resetLink'>$resetLink</a><br>Ссылка действительна 1 час.";
            $mail->AltBody = "Для сброса пароля перейдите по ссылке: $resetLink. Ссылка действительна 1 час.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mail error:' . $mail->ErrorInfo);
            return false;
        }
    }
}
