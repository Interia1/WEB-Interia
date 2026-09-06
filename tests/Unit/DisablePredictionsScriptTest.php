<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DisablePredictionsScriptTest extends TestCase
{
    public function test_script_keeps_chat_enabled_while_disabling_editor_predictions(): void
    {
        $tempRoot = sys_get_temp_dir().'/web-interia-disable-predictions-'.bin2hex(random_bytes(8));
        $addonsDirectory = $tempRoot.'/doplnky';
        $scriptDirectory = $addonsDirectory.'/vypnutie-predikcie-chatu';
        $settingsDirectory = $tempRoot.'/.vscode';

        $this->assertTrue(mkdir($scriptDirectory, 0777, true));
        $this->assertTrue(mkdir($settingsDirectory, 0777, true));

        try {
            $this->assertTrue(copy(
                dirname(__DIR__, 2).'/doplnky/vypnutie-predikcie-chatu/disable-predictions.php',
                $scriptDirectory.'/disable-predictions.php',
            ));

            $this->assertNotFalse(file_put_contents(
                $settingsDirectory.'/settings.json',
                json_encode([
                    'chat.disableAIFeatures' => true,
                    'workbench.colorTheme' => 'Default Dark+',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
            ));

            exec(
                sprintf('php %s 2>&1', escapeshellarg($scriptDirectory.'/disable-predictions.php')),
                $output,
                $exitCode,
            );

            $settings = json_decode((string) file_get_contents($settingsDirectory.'/settings.json'), true);

            $this->assertSame(0, $exitCode);
            $this->assertSame(['[web-interia] Predikcia pisania v editore je vypnuta.'], $output);
            $this->assertIsArray($settings);
            $this->assertSame(false, $settings['chat.disableAIFeatures']);
            $this->assertSame(false, $settings['editor.inlineSuggest.enabled']);
            $this->assertSame(false, $settings['github.copilot.nextEditSuggestions.enabled']);
            $this->assertSame('Default Dark+', $settings['workbench.colorTheme']);
        } finally {
            @unlink($settingsDirectory.'/settings.json');
            @unlink($scriptDirectory.'/disable-predictions.php');
            @rmdir($settingsDirectory);
            @rmdir($scriptDirectory);
            @rmdir($addonsDirectory);
            @rmdir($tempRoot);
        }
    }
}
