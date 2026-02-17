<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;

    protected $table = 'research';

    protected $fillable = [
        'faculty_id',
        'title',
        'authors',
        'co_authors',
        'adviser',
        'published_date',
        'abstract',
        'file_path',
        'status',
        'download_count',
    ];

    protected $casts = [
        'published_date' => 'date',
        'download_count' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function downloadPermissions()
    {
        return $this->hasMany(DownloadPermission::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
