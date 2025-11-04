<div {{ $attributes }}>
    <form method="POST" action="{{ route('backstage.forms.submit', $form->slug) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="content_ulid" value="{{ $content->ulid }}">
        @foreach ($form->fields as $field)

            @if($field->config['hidden'] ?? false)
                <input type="hidden" name="{{ $field->slug }}" value="{{ old($field->slug, $field->default_value) }}">
            @else
            <div>

                <label for="{{ $field->slug }}">{{ $field->name }}</label>
                <input type="{{ $field->field_type == 'file-upload' ? 'file' : $field->field_type }}" name="{{ $field->slug }}" id="{{ $field->slug }}">
                @error($field->slug)
                    <p>{{ $message }}</p>
                @enderror

            </div>
            @endif
        @endforeach

        <button type="submit">{{ $form->submit_button ?? __('Submit') }}</button>
    </form>
</div>
