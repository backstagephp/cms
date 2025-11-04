<?php

namespace Backstage\Http\Middleware\Filament;

use Backstage\Models\Block;
use Backstage\Models\Content;
use Backstage\Models\Domain;
use Backstage\Models\Form;
use Backstage\Models\Language;
use Backstage\Models\Media;
use Backstage\Models\Menu;
use Backstage\Models\Setting;
use Backstage\Models\Tag;
use Backstage\Models\Template;
use Backstage\Models\Type;
use Backstage\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ScopedBySite
{
    public function handle(Request $request, Closure $next)
    {
        Block::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Content::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Domain::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Form::addGlobalScope(
            fn (Builder $query) => $query->whereHas('site', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Language::addGlobalScope(
            fn (Builder $query) => $query->whereHas('domains.site', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        // Media::addGlobalScope(
        //     fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        // );

        Menu::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Setting::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Tag::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Template::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Type::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        User::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        return $next($request);
    }
}
