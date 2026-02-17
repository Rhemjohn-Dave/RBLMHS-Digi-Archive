<?php

namespace App\Notifications;

use App\Models\DownloadPermission;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DownloadRequestProcessed extends Notification
{
    public function __construct(
        public DownloadPermission $permission,
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
        $research = $this->permission->research;
        $title = $research ? $research->title : 'Research';
        $approved = $this->status === 'approved';
        return [
            'type' => 'download_request_processed',
            'title' => $approved ? 'Download request approved' : 'Download request declined',
            'message' => $approved
                ? "You may now download \"{$title}\"."
                : "Your request to download \"{$title}\" was declined.",
            'link' => $research ? "/research/{$research->id}" : '/research',
            'research_id' => $research?->id,
            'status' => $this->status,
        ];
    }
}
