<?php

namespace Backstage\Models;

use Backstage\Redirects\Laravel\Models\Redirect as ModelsRedirect;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redirect extends ModelsRedirect
{
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->site_id = Filament::getTenant()->ulid;
        });
    }
}
