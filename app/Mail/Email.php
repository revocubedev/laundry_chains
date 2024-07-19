<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
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
        $FROM_EMAIL = env('MAIL_FROM_ADDRESS');
        $FROM_NAME = env('MAIL_FROM_NAME');
        $subject = $this->data['subject'];
        $template = $this->data['template'];
        $content = $this->data['content'] ?? [];

        return $this->from($FROM_EMAIL, $FROM_NAME)
            ->subject($subject)
            ->markdown($template, $content);
    }
}
