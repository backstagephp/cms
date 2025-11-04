@if($getState() && file_exists(base_path('vendor/backstage/cms/resources/img/flags/' . $getState() . '.svg')))
<div class="gap-y-1 px-3 py-4 w-full fi-ta-text">
    <img
        src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . $getState() . '.svg'))) }}"
        alt="{{ localized_country_name($getState()) }}"
        title="{{ localized_country_name($getState()) }}"
        class="inline-block w-5 h-5"
    >
</div>
@endif
