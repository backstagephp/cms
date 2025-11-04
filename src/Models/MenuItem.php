<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class MenuItem extends Model
{
    use HasPackageFactory;
    use HasRecursiveRelationships;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_slug', 'slug');
    }

    public function getParentKeyName()
    {
        return 'parent_ulid';
    }
}
