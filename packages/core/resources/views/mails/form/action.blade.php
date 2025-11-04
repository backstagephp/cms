<p>Dear,</p>
<p>The following information has been submitted through {{ $form->name ?? $action->form_slug }}:</p>
<p>
    @foreach ($submission->values as $value)
        <strong>{{ $value->field?->name }}:</strong> {{ $value->value }}<br>
    @endforeach
</p>
@if ($action->config['body'] ?? null)
<p>
    {{ $action->config['body'] }}
</p>
@endif