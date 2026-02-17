<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadPermission extends Model
{
    protected $fillable = [
        'user_id',
        'research_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function research(): BelongsTo
    {
        return $this->belongsTo(Research::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
