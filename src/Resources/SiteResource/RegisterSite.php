<?php

namespace Backstage\Resources\SiteResource;

use Backstage\Models\Site;
use Backstage\Resources\SiteResource;
use Filament\Actions\Action;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;

class RegisterSite extends RegisterTenant
{
    protected string $view = 'backstage::filament.sites.register';

    public static function getLabel(): string
    {
        return 'Add new Site';
    }

    public static function canView(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return SiteResource::form($schema, fullWidth: true);
    }

    protected function handleRegistration(array $data): Site
    {
        $site = Site::create($data);

        $site->users()->attach(auth()->user());

        $domain = $site->domains()->create([
            'name' => parse_url(config('app.url'), PHP_URL_HOST),
            'environment' => config('app.env'),
        ]);

        $domain->languages()->create([
            'code' => config('app.locale'),
            'name' => config('app.locale'),
            'native' => config('app.locale'),
            'active' => true,
            'default' => true,
        ]);

        $type = $site->types()->create([
            'name' => 'Page',
            'name_plural' => 'Pages',
            'slug' => 'page',
            'icon' => 'circle-stack',
            'public' => true,
        ]);

        $type->fields()->createMany([
            [
                'name' => 'Title',
                'slug' => 'title',
                'field_type' => 'text',
                'position' => 1,
            ],
            [
                'name' => 'Content',
                'slug' => 'content',
                'field_type' => 'rich-editor',
                'position' => 2,
            ],
        ]);

        return $site;
    }

    public function getGoBackAction(): Action
    {
        return Action::make('goBack')
            ->label(__('Go back'))
            ->link()
            ->icon('heroicon-s-arrow-left')
            ->color('gray')
            ->url(url()->previous());
    }
}
