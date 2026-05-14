<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TournamentCreated extends Notification
{
    use Queueable;

    public $tournament;

    /**
     * Create a new notification instance.
     */
    public function __construct($tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tournament_id' => $this->tournament->id,
            'title' => 'Giải đấu mới: ' . $this->tournament->name,
            'message' => 'Một giải đấu mới vừa được mở đăng ký. Hãy tham gia ngay!',
            'type' => 'tournament_created',
        ];
    }
}
