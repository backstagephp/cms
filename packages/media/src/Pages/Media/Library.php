
<?php

namespace Backstage\Media\Pages\Media;

use Backstage\Media\MediaPlugin;
use Backstage\Media\Models\Media;
use Backstage\Media\Resources\MediaResource;
use Backstage\Media\Resources\MediaResource\CreateMedia;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

class Library extends Page implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    protected static string | \UnitEnum | null $navigationGroup = 'Media';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 0;

    protected string $view = 'media::pages.media.library';

    protected static ?string $slug = 'media-library';

    public ?Media $selectedMedia = null;

    public ?Collection $media = null;

    public function getHeading(): string
    {
        return __('Media Library');
    }

    public function mount(): void
    {
        $this->loadMedia();
    }

    public function loadMedia(): void
    {
        // $startDate = $this->filters['startDate'] ?? null;
        // $endDate = $this->filters['endDate'] ?? null;

        $query = Media::orderBy('created_at', 'desc');

        // if ($startDate && $endDate) {
        //     $query->whereBetween('created_at', [$startDate, $endDate]);
        // }

        $media = $query->get();

        $this->media = $media;
    }

    protected function getActions(): array
    {
        return [
            Action::make('upload')
                ->label(__('Upload'))
                ->schema(function (Schema $schema) {
                    return MediaResource::form($schema);
                })
                ->action(function (array $data) {
                    $createMedia = new CreateMedia;
                    $createMedia->handleRecordCreation($data);

                    $this->loadMedia();

                    Notification::make()
                        ->title(__('Uploaded media'))
                        ->success()
                        ->send();
                })
                ->modalSubmitActionLabel(__('Upload'))
                ->color('primary')
                ->modal()
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return MediaPlugin::get()->getNavigationLabel() ?? __('Media Library');
    }

    public static function getNavigationIcon(): string
    {
        return MediaPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return MediaPlugin::get()->getNavigationSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return MediaPlugin::get()->getNavigationGroup();
    }

    public function setMedia(string $mediaId): void
    {
        $media = Media::find($mediaId);

        if ($media) {
            $this->selectedMedia = $media;
        }
    }

    public function showMediaAction(): Action
    {
        return Action::make('showMedia')
            ->label(__('File details'))
            ->modalContent(function (array $arguments) {
                $this->setMedia($arguments['ulid']);

                return view('media::pages.media.overlay', [
                    'media' => $this->selectedMedia,
                ]);
            })
            ->modalFooterActions([
                $this->downloadAction(),
                $this->deleteAction(),
            ])
            ->modalWidth(Width::TwoExtraLarge)
            ->slideOver();
    }

    public function downloadAction(): Action
    {
        return Action::make('download')
            ->label(__('Download'))
            ->color('gray')
            ->action(function () {
                if ($this->selectedMedia) {
                    return response()->download($this->selectedMedia->src);
                }
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('Delete'))
            ->color('danger')
            ->requiresConfirmation()
            ->action(function () {
                if ($this->selectedMedia) {
                    $this->selectedMedia->delete();

                    $this->loadMedia();

                    Notification::make()
                        ->title(__('Deleted media'))
                        ->success()
                        ->send();
                }
            });
    }
}
