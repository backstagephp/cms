<?php

namespace Backstage;

use Backstage\Models\User;
use Exception;
use Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class BackstageAvatarProvider extends UiAvatarsProvider
{
    public function get(Model | Authenticatable $record): string
    {
        if ($record instanceof User) {
            $name = str(Filament::getNameForDefaultAvatar($record))
                ->trim()
                ->explode(' ')
                ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
                ->join(' ');

            if (str($name)->contains('(') || str($name)->contains(')')) {
                $name = str($name)
                    ->replace('(', '')
                    ->replace(')', '');
            }

            $backgroundColor = '#242325';

            if ($this->hasGravatar($record->email)) {
                return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($record->email))) . '?s=200';
            }

            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=' . $backgroundColor . '&size=200&bold=true&rounded=true&';
        }

        return parent::get($record);
    }

    public function hasGravatar(string $email): bool
    {
        $hash = md5(strtolower(trim($email)));
        $cacheKey = "gravatar_exists_{$hash}";

        return cache()->remember($cacheKey, 3600, function () use ($hash) {
            $url = "https://www.gravatar.com/avatar/{$hash}?d=404";

            try {
                $response = Http::withoutRedirecting()->head($url);

                return $response->status() === 200;
            } catch (Exception $e) {
                return false;
            }
        });
    }
}
