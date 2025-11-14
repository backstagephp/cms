@if($getState() && file_exists(flag_path($getState())))
<div class="gap-y-1 px-3 py-4 w-full fi-ta-text">
    <img
        src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(flag_path($getState()))) }}"
        alt="{{ localized_language_name($getState()) }}"
        title="{{ localized_language_name($getState()) }}"
        class="inline-block w-5 h-5"
    >
</div>
@endif
