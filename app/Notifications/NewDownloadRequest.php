<?php

namespace App\Notifications;

use App\Models\DownloadPermission;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewDownloadRequest extends Notification
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
        $title = $research ? $research->title : 'a research';
        return [
            'type' => 'download_request',
            'title' => 'Download permission requested',
            'message' => "{$requester} requested to download \"{$title}\".",
            'link' => '/admin/download-requests',
            'permission_id' => $this->permission->id,
        ];
    }
}
