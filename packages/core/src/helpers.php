<?php

use Backstage\Models\Setting;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $keys = explode('.', $key);
        $setting = Setting::where('slug', $keys[0] ?? null)->with('fields')->first();

        if (! $setting) {
            return $default;
        }

        if (! isset($keys[1])) {
            return $setting->setting();
        }

        return $setting->setting($keys[1]) ?? $default;
    }
}

if (! function_exists('flag_path')) {
    function flag_path(string $code): string
    {
        $vendorPath = base_path('vendor/backstage/cms/resources/img/flags/' . $code . '.svg');

        if (file_exists($vendorPath)) {
            return $vendorPath;
        }

        return dirname(base_path()) . '/cms/packages/core/resources/img/flags/' . $code . '.svg';
    }
}
