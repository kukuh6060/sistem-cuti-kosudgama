<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanStatusNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $url;
    protected $icon;

    /**
     * Buat instance notifikasi baru.
     *
     * @param string $message Pesan notifikasi
     * @param string $url URL untuk diklik
     * @param string $icon (Opsional) 'success', 'danger', 'info'
     */
    public function __construct(string $message, string $url = '#', string $icon = 'info')
    {
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
    }

    /**
     * Tentukan via apa notifikasi dikirim.
     */
    public function via(object $notifiable): array
    {
        // Kita hanya akan simpan di database
        return ['database'];
    }

    /**
     * Dapatkan representasi data (database) dari notifikasi.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}