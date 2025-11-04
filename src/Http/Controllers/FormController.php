<?php

namespace Backstage\Http\Controllers;

use Backstage\Events\FormSubmitted;
use Backstage\Models\Content;
use Backstage\Models\Form;
use Illuminate\Http\Request;

class FormController
{
    public function submit(Request $request, Form $form)
    {
        $request->validate(
            $form->fields->mapWithKeys(function ($field) {
                $rules = [];
                if ($field->config['required'] ?? false) {
                    $rules[] = 'required';
                }

                return [$field->slug => $rules];
            })
                ->filter()
                ->merge(['content_ulid' => ['nullable', 'exists:content,ulid']])
                ->toArray()
        );

        $content = Content::where('ulid', $request->input('content_ulid'))->first();

        $submission = $form->submissions()->create([
            'site_ulid' => $content?->site_ulid ?? null,
            'language_code' => $content?->language_code ?? null,
            'content_ulid' => $content?->ulid ?? null,
            'submitted_by' => $request->user()?->ulid ?? null,
            'ip_address' => $request->ip(),
            'hostname' => $request->server('REMOTE_HOST'),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        $submission->values()->createMany(
            $form->fields->map(function ($field) use ($request) {
                $value = $request->input($field->slug);
                if ($field->field_type == 'file-upload') {
                    $result = $request->file($field->slug)->store();
                    $value = json_encode([
                        'path' => $result,
                        'name' => $request->file($field->slug)->getClientOriginalName(),
                        'size' => $request->file($field->slug)->getSize(),
                        'type' => $request->file($field->slug)->getMimeType(),
                    ]);
                } elseif (is_array($value)) {
                    $value = json_encode($value);
                }

                return [
                    'field_ulid' => $field->ulid,
                    'value' => $value,
                ];
            })->toArray()
        );

        FormSubmitted::dispatch($submission);

        $url = $form->formActions()->where('type', 'redirect')->first()?->config['url'] ?? null;

        if ($url) {
            $url = str_replace('{submission}', encrypt($submission->ulid), $url);

            return redirect($url);
        }

        return redirect()->back()
            ->with('success', __('Your submission has been received.'))
            ->with('submission', $submission);
    }
}
