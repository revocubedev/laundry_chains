<?php

namespace App\Services\Helpers;

use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class MailService
{
    private function send($data)
    {
        Mail::to($data['to'])->send(new Email($data));
    }

    private function sendQueue($data)
    {
        Mail::to($data['to'])->queue(new Email($data));
    }

    public function sendStaffAddEmail($data)
    {
        $emailData = [
            'from_email' => $data['from_email'],
            'from_name' => $data['from_name'],
            'to' => $data['to'],
            'subject' => 'Your Login Credentials',
            'template' => 'emails.staff_addup',
            'content' => $data['content'] ?? null,
        ];

        $this->send($emailData);
    }

    public function sendWelcomeEmail($data)
    {
        $emailData = [
            'to' => $data['to'],
            'subject' => 'Welcome to Laundry Chains',
            'template' => 'emails.welcome-email',
            'content' => $data['content'] ?? null,
        ];

        $this->send($emailData);
    }
}
