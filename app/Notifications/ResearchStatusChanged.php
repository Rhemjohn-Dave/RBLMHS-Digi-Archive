<?php

namespace App\Notifications;

use App\Models\Research;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ResearchStatusChanged extends Notification
{
    public function __construct(
        public Research $research,
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
        $title = $this->research->title;
        return [
            'type' => 'research_status_changed',
            'title' => $approved ? 'Research approved' : 'Research not approved',
            'message' => $approved
                ? "Your submission \"{$title}\" is now published in the archive."
                : "Your submission \"{$title}\" was not approved for publication.",
            'link' => '/my-submissions',
            'research_id' => $this->research->id,
            'status' => $this->status,
        ];
    }
}
