<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'lrn',
        'faculty_number',
        'family_name',
        'given_name',
        'middle_name',
        'username',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isFaculty(): bool
    {
        return $this->role === 'faculty';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function fullName(): string
    {
        return trim("{$this->given_name} {$this->middle_name} {$this->family_name}");
    }

    public function research()
    {
        return $this->hasMany(Research::class, 'faculty_id');
    }

    public function downloadPermissions()
    {
        return $this->hasMany(DownloadPermission::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }
}
