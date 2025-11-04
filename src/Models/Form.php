<?php

namespace Backstage\Models;

use Backstage\Fields\Models\Field;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Form extends Model
{
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($form) {
            $form->fields()->delete();
        });
        static::updated(function ($form) {
            if ($form->isDirty('slug')) {
                Field::where([
                    'model_type' => 'form',
                    'model_key' => $form->getOriginal('slug'),
                ])->update(['model_key' => $form->slug]);
            }
        });
    }

    protected function casts(): array
    {
        return [];
    }

    public function formActions(): HasMany
    {
        return $this->hasMany(FormAction::class);
    }

    public function fields(): MorphMany
    {
        return $this->morphMany(Field::class, 'model', 'model_type', 'model_key', 'slug')
            ->orderBy('position');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_slug', 'slug');
    }
}
