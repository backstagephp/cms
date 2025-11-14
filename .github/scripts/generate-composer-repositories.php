<?php

$packagesDir = $argv[1] ?? 'packages';
$branch = $argv[2] ?? null;

// Get branch from git if not provided
if ($branch === null) {
    $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?: 'dev-main');
}

$files = glob($packagesDir . '/*', GLOB_ONLYDIR);

$packageNames = [];
$repositories = [];
$packageNamesMap = [];

foreach($files as $file) {
    $composerFile = $file . '/composer.json';
    if (file_exists($composerFile)) {
        $packageComposer = json_decode(file_get_contents($composerFile), true);
        if (isset($packageComposer['name'])) {
            $packageName = $packageComposer['name'];
            $packageNames[] = $packageName;
            $packageNamesMap[$packageName] = $file;
            $repositories[] = [
                'type' => 'path',
                'url' => ltrim($file, './'),
                'canonical' => false,
                'reference' => $branch,
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

// Remove all VCS repositories from package composer.json files - packages must come from path repositories
foreach($packageNamesMap as $packageName => $packagePath) {
    $packageComposerFile = $packagePath . '/composer.json';
    $packageComposer = json_decode(file_get_contents($packageComposerFile), true);
    
    if (isset($packageComposer['repositories']) && is_array($packageComposer['repositories'])) {
        $filteredRepos = [];
        foreach($packageComposer['repositories'] as $key => $repo) {
            // Skip all VCS/git repositories - packages must come from path repositories
            if (isset($repo['type']) && ($repo['type'] === 'git' || $repo['type'] === 'vcs')) {
                continue;
            }
            // Keep non-VCS repositories (like path, composer, etc.)
            $filteredRepos[$key] = $repo;
        }
        $packageComposer['repositories'] = $filteredRepos;
        file_put_contents(
            $packageComposerFile,
            json_encode($packageComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}

// Output package names for use in workflow
echo implode(' ', $packageNames);