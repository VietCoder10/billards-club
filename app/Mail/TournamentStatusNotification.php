<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TournamentStatusNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     */

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->data['status'] == 1 
            ? '[' . env('APP_NAME') . '] Đăng ký giải đấu thành công' 
            : '[' . env('APP_NAME') . '] Đăng ký giải đấu bị từ chối';

        $view = $this->data['status'] == 1 
            ? 'mails.tournament_accepted' 
            : 'mails.tournament_rejected';

        return $this->subject($subject)
            ->view($view)
            ->with([
                'data' => $this->data,
            ]);
    }
}
