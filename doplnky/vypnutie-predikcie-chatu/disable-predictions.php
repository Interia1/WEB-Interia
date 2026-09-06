<?php

declare(strict_types=1);

$rootDirectory = dirname(__DIR__, 2);
$settingsDirectory = $rootDirectory.'/.vscode';
$settingsFile = $settingsDirectory.'/settings.json';

if (! is_dir($settingsDirectory) && ! mkdir($settingsDirectory, 0775, true) && ! is_dir($settingsDirectory)) {
    fwrite(STDERR, "Nepodarilo sa vytvorit priecinok .vscode.\n");
    exit(1);
}

$settings = [];

if (is_file($settingsFile)) {
    $decodedSettings = json_decode((string) file_get_contents($settingsFile), true);

    if (! is_array($decodedSettings)) {
        fwrite(STDERR, "Subor .vscode/settings.json neobsahuje platny JSON.\n");
        exit(1);
    }

    $settings = $decodedSettings;
}

$settings['chat.disableAIFeatures'] = false;
$settings['editor.inlineSuggest.enabled'] = false;
$settings['github.copilot.nextEditSuggestions.enabled'] = false;

$encodedSettings = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

if (file_put_contents($settingsFile, $encodedSettings) === false) {
    fwrite(STDERR, "Nepodarilo sa zapisat .vscode/settings.json.\n");
    exit(1);
}

fwrite(STDOUT, "[web-interia] Predikcia pisania v editore je vypnuta.\n");