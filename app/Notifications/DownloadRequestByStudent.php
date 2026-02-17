<?php

namespace App\Notifications;

use App\Models\DownloadPermission;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies the faculty who owns the research when a student requests download.
 */
class DownloadRequestByStudent extends Notification
{
    public function __construct(
        public DownloadPermission $permission
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
        $user = $this->permission->user;
        $research = $this->permission->research;
        $requester = $user ? $user->fullName() : 'A student';
        $title = $research ? $research->title : 'your research';
        return [
            'type' => 'download_request_faculty',
            'title' => 'Download requested for your research',
            'message' => "{$requester} requested to download \"{$title}\". An admin will process the request.",
            'link' => '/my-submissions',
            'research_id' => $research?->id,
        ];
    }
}
