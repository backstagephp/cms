<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Backstage\Shared\HasTags;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Site extends Model
{
    use HasPackageFactory;
    use HasTags;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function default(): ?Site
    {
        return static::firstWhere('default', 1);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function languages(): HasManyThrough
    {
        return $this->hasManyThrough(Language::class, Domain::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(Type::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function blocks(): BelongsToMany
    {
        return $this->belongsToMany(Block::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function redirects(): HasMany
    {
        return $this->hasMany(Redirect::class);
    }
}
