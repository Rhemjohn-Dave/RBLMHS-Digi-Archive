<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationRequest extends Notification
{
    public function __construct(
        public User $newUser
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
        $name = $this->newUser->fullName();
        return [
            'type' => 'registration_request',
            'title' => 'New registration request',
            'message' => "{$name} ({$this->newUser->username}) has requested to join as {$this->newUser->role}.",
            'link' => '/admin/users?status=pending',
            'user_id' => $this->newUser->id,
        ];
    }
}
