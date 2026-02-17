<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadLog extends Model
{
    protected $fillable = [
        'user_id',
        'research_id',
        'downloaded_at',
        'ip_address',
        'role_at_time',
    ];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }
}
