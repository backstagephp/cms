<?php

$packagesDir = $argv[1] ?? 'packages';
$files = glob($packagesDir . '/*', GLOB_ONLYDIR);

$packageNames = [];
$repositories = [];

foreach($files as $file) {
    $composerFile = $file . '/composer.json';
    if (file_exists($composerFile)) {
        $packageComposer = json_decode(file_get_contents($composerFile), true);
        if (isset($packageComposer['name'])) {
            $packageNames[] = $packageComposer['name'];
            $repositories[] = [
                'type' => 'path',
                'url' => ltrim($file, './'),
                'canonical' => false,
            ];
        }
    }
}

$composer = json_decode(file_get_contents('composer.json'), true);

$composer['repositories'] = $repositories;


file_put_contents(
    'composer.json',
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

// Output package names for use in workflow
echo implode(' ', $packageNames);