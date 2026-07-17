<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiryDatabaseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $batchCode;
    public $namaBarang;
    public $status;
    public $pesan;

    public function __construct($batchCode, $namaBarang, $status, $pesan)
    {
        $this->batchCode = $batchCode;
        $this->namaBarang = $namaBarang;
        $this->status = $status;
        $this->pesan = $pesan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'batch_code'  => $this->batchCode,
            'nama_barang' => $this->namaBarang,
            'status'      => $this->status,
            'pesan'       => $this->pesan,
        ];
    }
}
