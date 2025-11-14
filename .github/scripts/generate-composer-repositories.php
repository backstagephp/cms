<?php

$files = glob('packages/*', GLOB_ONLYDIR);

$repositories = [];

foreach($files as $file) {
    $repositories[] = [
        'type' => 'path',
        'url' => ltrim($file, './'),
    ];
}

$composer = json_decode(file_get_contents('composer.json'), true);

$composer['repositories'] = $repositories;

file_put_contents(
    'composer.json',
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);