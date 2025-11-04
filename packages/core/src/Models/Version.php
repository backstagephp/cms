<?php

namespace Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Version extends Model
{
    protected $table = 'content_versions';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_ulid', 'ulid');
    }
}
