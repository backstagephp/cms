<?php

namespace Backstage\Models;

use Backstage\Fields\Models\Field;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmissionValue extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $with = ['field'];

    protected function casts(): array
    {
        return [];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
