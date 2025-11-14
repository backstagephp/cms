<?php

namespace Backstage\Announcements\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementDismissal extends Model
{
    protected $fillable = [
        'user_id',
        'announcement_id',
        'dismissed_at',
    ];

    protected $casts = [
        'dismissed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $dismissal) {
            if (is_null($dismissal->dismissed_at)) {
                $dismissal->dismissed_at = now();
            }

            if (is_null($dismissal->user_id) && Filament::auth()->check()) {
                $dismissal->user_id = Filament::auth()->id();
            }
        });
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }
}
