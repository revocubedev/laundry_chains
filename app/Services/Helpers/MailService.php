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

    public function sendCreationNotificationEmail($data)
    {
        $emailData = [
            'to' => $data['to'],
            'subject' => 'Your Order is created',
            'template' => 'emails.create_notification',
            'content' => $data['content'] ?? null,
        ];

        $this->sendQueue($emailData);
    }

    public function pickUpEmail($data)
    {
        $emailData = [
            'to' => $data['to'],
            'subject' => 'Your Order is completed',
            'template' => 'emails.pickup_notification',
            'content' => $data['content'] ?? null
        ];

        $this->sendQueue($emailData);
    }

    public function completeNotificationEmail($data)
    {
        $emailData = [
            'to' => $data['to'],
            'subject' => 'Your Order has been picked up',
            'template' => 'emails.completed_notification',
            'content' => $data['content'] ?? null
        ];

        $this->sendQueue($emailData);
    }
}
