<?php

namespace Backstage\Media\Resources\MediaResource;

use Backstage\Media\MediaPlugin;
use Backstage\Media\Models\Media;
use Exception;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    public static function getResource(): string
    {
        return MediaPlugin::get()->getResource();
    }

    public function handleRecordCreation(array $data): Model
    {
        $firstMedia = null;

        foreach ($data['media'] as $file) {
            // Get the full path on the configured disk
            $fullPath = Storage::disk(config('backstage.media.disk'))->path($file);

            $filename = basename($file);

            $mimeType = Storage::disk(config('backstage.media.disk'))->mimeType($file);

            $fileSize = Storage::disk(config('backstage.media.disk'))->size($file);

            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            // Additional file information
            $fileInfo = [
                'full_path' => $fullPath,
                'filename' => $filename,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ];

            if (str_starts_with($mimeType, 'image/')) {
                try {
                    $imageSize = getimagesize($fullPath);
                    $fileInfo += [
                        'width' => $imageSize[0] ?? null,
                        'height' => $imageSize[1] ?? null,
                        'image_type' => $imageSize[2] ?? null,
                    ];
                } catch (Exception $e) {
                    // Log or handle image size extraction error
                }
            }

            $tenant = Filament::getTenant();
            $media = Media::create([
                'site_ulid' => $tenant && property_exists($tenant, 'ulid') ? $tenant->ulid : null,
                'disk' => config('backstage.media.disk'),
                'uploaded_by' => auth()->id(),
                'filename' => $filename,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'width' => $fileInfo['width'] ?? null,
                'height' => $fileInfo['height'] ?? null,
                'checksum' => md5_file($fullPath),
                'public' => config('backstage.media.visibility') === 'public', // TODO: Should be configurable in the form itself
            ]);

            if ($firstMedia === null) {
                $firstMedia = $media;
            }
        }

        return $firstMedia ?? Media::first();
        // return static::getModel()::create($data);
    }
}
