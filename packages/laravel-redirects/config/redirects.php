<?php

return [
    /*
     * The available status codes for redirection.
     */
    'status_codes' => [
        // 300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        // 303 => 'See Other',
        // 304 => 'Not Modified',
        // 305 => 'Use Proxy',
        // 306 => 'Unused',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
    ],

    /*
     * Define the model you want to use for Redirects.
     */
    'model' => Backstage\Redirects\Laravel\Models\Redirect::class,

    /*
     * The default status code to select when redirecting.
     */
    'default_status_code' => env('REDIRECT_DEFAULT_STATUS_CODE', 301),

    /*
     * If the case is sensitive, the following routes will be treated as different:
     *
     * /example
     * /Example
     */
    'case_sensitive' => env('REDIRECT_CASE_SENSITIVE', false),

    /*
     * If the trailing slash is sensitive, the following routes will be treated as different:
     *
     * /example
     * /example/
     */
    'trailing_slash_sensitive' => env('REDIRECT_TRAILING_SLASH_SENSITIVE', false),

    /*
     * Always add a trailing slash for redirect endpoint to maintain consistency in used URLs for SEO.
    */
    'trailing_slash' => env('REDIRECT_WITH_TRAILING_SLASH', false),

    /**
     * The middleware to use for redirecting.
     * If you ad \Backstage\Redirects\Laravel\Http\Middleware\BackstageRedirects, the request will be looped endlessly.
     */
    'middleware' => [
        Backstage\Redirects\Laravel\Http\Middleware\HttpRedirects::class,
        Backstage\Redirects\Laravel\Http\Middleware\WildRedirects::class,
        Backstage\Redirects\Laravel\Http\Middleware\StrictRedirects::class,
    ],
];
