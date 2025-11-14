<?php

namespace Backstage\Announcements\Models;

use Backstage\Announcements\Livewire\Announcement as LivewireAnnouncement;
use Filament\Facades\Filament;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;

class Announcement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'scopes',
        'color',
    ];

    protected $casts = [
        'scopes' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $announcement) {
            $announcement->scopes = ! empty($announcement->scopes) ? $announcement->scopes : ['*'];
        });
    }

    public function render(string $scope): Htmlable
    {
        $livewire = Livewire::mount(LivewireAnnouncement::class, ['announcement' => $this, 'scope' => $scope]);

        return new HtmlString($livewire);
    }

    public function dismissals(): HasMany
    {
        return $this->hasMany(AnnouncementDismissal::class);
    }

    public function isDismissedBy(?int $userId = null): bool
    {
        if (! Filament::auth()->check()) {
            return false;
        }

        if (is_null($userId)) {
            $userId = Filament::auth()->user()->id;
        }

        return $this->dismissals()
            ->where('user_id', $userId)
            ->exists();
    }
}
