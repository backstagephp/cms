<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function site(): BelongsTo
    {
        return $this->BelongsTo(Site::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function values()
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_ulid', 'ulid');
    }

    public function value($fieldSlug)
    {
        return $this->values()->whereHas('field', function ($query) use ($fieldSlug) {
            return $query->where('slug', $fieldSlug);
        })->first()?->value;
    }
}
