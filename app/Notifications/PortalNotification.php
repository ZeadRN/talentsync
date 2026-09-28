<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $message, public string $path)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return config('broadcasting.default') === 'pusher' ? ['database', 'mail', 'broadcast'] : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('TalentSync update')->line($this->message)->action('Open TalentSync', url($this->path));
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => $this->message, 'path' => $this->path];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
