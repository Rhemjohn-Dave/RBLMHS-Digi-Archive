<?php

namespace App\Notifications;

use App\Models\Research;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewResearchUploaded extends Notification
{
    public function __construct(
        public Research $research
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
        $faculty = $this->research->faculty;
        $uploader = $faculty ? $faculty->fullName() : 'A faculty member';
        return [
            'type' => 'research_uploaded',
            'title' => 'New research submission',
            'message' => "{$uploader} submitted \"{$this->research->title}\" for approval.",
            'link' => '/admin/research',
            'research_id' => $this->research->id,
        ];
    }
}
