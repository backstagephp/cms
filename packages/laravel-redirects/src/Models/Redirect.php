<?php

namespace Backstage\Redirects\Laravel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Redirect extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $fillable = [
        'source',
        'destination',
        'code',
    ];

    public function redirect(Request $request): ?RedirectResponse
    {
        $this->increment('hits');

        $destination = $this->destination;

        if ($request->query()) {
            $destination .= (str($destination)->contains('?') ? '&' : '?') . Arr::query($request->query());
        }

        return redirect($destination, $this->code)
            ->with('input', $request->input());
    }
}
