<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserStatusChanged extends Notification
{
    public function __construct(
        public string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->status === 'approved';
        return [
            'type' => 'user_status_changed',
            'title' => $approved ? 'Account approved' : 'Account not approved',
            'message' => $approved
                ? 'Your registration has been approved. You can now use the archive.'
                : 'Your registration was not approved. Contact an administrator if you have questions.',
            'link' => '/',
            'status' => $this->status,
        ];
    }
}
