<?php

// app/Notifications/EmailOtpNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notifikasi pengiriman OTP ke email untuk verifikasi akun baru.
 * OTP dibangkitkan di controller dan disimpan di cache sebelum notifikasi ini dikirim.
 */
class EmailOtpNotification extends Notification
{
    /**
     * @param string $otp Kode OTP 6 digit yang akan dikirim.
     */
    public function __construct(protected string $otp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi Email Anda')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Gunakan kode OTP berikut untuk memverifikasi akun Anda:')
            ->line('**' . $this->otp . '**')
            ->line('Kode ini berlaku selama **10 menit**. Jangan bagikan kode ini kepada siapa pun.')
            ->line('Jika Anda tidak merasa mendaftar, abaikan email ini.');
    }
}
