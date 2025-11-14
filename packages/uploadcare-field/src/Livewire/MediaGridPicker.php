<?php

namespace Backstage\UploadcareField\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MediaGridPicker extends Component
{
    use WithPagination;

    public string $fieldName;

    public int $perPage = 12;

    public ?string $selectedMediaId = null;
    
    public ?string $selectedMediaUuid = null;
    
    public string $search = '';

    public function mount(string $fieldName, int $perPage = 12): void
    {
        $this->fieldName = $fieldName;
        $this->perPage = $perPage;
    }

    #[Computed]
    public function mediaItems(): LengthAwarePaginator
    {
        $mediaModel = config('backstage.media.model', 'Backstage\\Models\\Media');

        $query = $mediaModel::query();

        // Apply search filter
        if (!empty($this->search)) {
            $query->where('original_filename', 'like', '%' . $this->search . '%');
        }

        return $query->paginate($this->perPage)
            ->through(function ($media) {
                // Decode metadata if it's a JSON string
                $metadata = is_string($media->metadata) ? json_decode($media->metadata, true) : $media->metadata;

                return [
                    'id' => $media->ulid,
                    'filename' => $media->original_filename,
                    'mime_type' => $media->mime_type,
                    'is_image' => $media->mime_type && str_starts_with($media->mime_type, 'image/'),
                    'cdn_url' => $metadata['cdnUrl'] ?? null,
                    'width' => $media->width,
                    'height' => $media->height,
                ];
            });
    }

    public function updatePerPage(int $newPerPage): void
    {
        $this->perPage = $newPerPage;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectMedia(array $media): void
    {
        $this->selectedMediaId = $media['id'];

        // Extract UUID from CDN URL
        $cdnUrl = $media['cdn_url'] ?? null;
        $uuid = $cdnUrl;

        if ($cdnUrl && str_contains($cdnUrl, 'ucarecdn.com/')) {
            if (preg_match('/ucarecdn\.com\/([^\/\?]+)/', $cdnUrl, $matches)) {
                $uuid = $matches[1];
            }
        }

        // Store the UUID in the component state
        $this->selectedMediaUuid = $uuid;

        // Dispatch event to update hidden field in modal
        $this->dispatch(
            'set-hidden-field',
            fieldName: 'selected_media_uuid',
            value: $uuid
        );
    }

    public function render()
    {
        return view('backstage-uploadcare-field::livewire.media-grid-picker');
    }
}
