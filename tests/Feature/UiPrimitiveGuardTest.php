<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guardrail ALFATIH UI: file Blade fitur TIDAK BOLEH memperkenalkan
 * kontrol mentah browser/OS (select/date/file/confirm bawaan).
 * Rumah resmi primitif: resources/views/components/{ui,admin}/*.
 */
class UiPrimitiveGuardTest extends TestCase
{
    private function featureFiles(): array
    {
        $base = base_path('resources/views');
        $files = [];
        foreach (['admin', 'portal', 'public', 'partials', 'errors'] as $dir) {
            $path = $base.DIRECTORY_SEPARATOR.$dir;
            if (! is_dir($path)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($it as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    #[DataProvider('forbiddenPatternProvider')]
    public function test_no_raw_native_controls_in_feature_views(string $pattern, string $reason): void
    {
        $offenders = [];
        foreach ($this->featureFiles() as $file) {
            $content = file_get_contents($file);
            // Abaikan komentar Blade yang mendokumentasikan larangan.
            $content = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
            if (preg_match($pattern, $content)) {
                $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Kontrol mentah terlarang ({$reason}):\n".implode("\n", $offenders)
        );
    }

    public static function forbiddenPatternProvider(): array
    {
        return [
            'raw select' => ['/<select[\s>]/i', 'pakai <x-ui.select>'],
            'native date' => ['/type="date"/i', 'pakai <x-ui.date-picker>'],
            'native datetime' => ['/type="datetime-local"/i', 'pakai <x-ui.datetime-picker>'],
            'native time' => ['/<input[^>]*type="time"/i', 'pakai <x-ui.time-picker>'],
            'raw file' => ['/<input[^>]*type="file"/i', 'pakai <x-ui.file-upload> / <x-ui.image-preview>'],
            'datalist' => ['/<datalist/i', 'pakai <x-ui.select searchable>'],
            'native confirm' => ['/(?<![A-Za-z])confirm\(/', 'pakai confirmDialog() branded'],
        ];
    }

    public function test_calendar_internal_buttons_are_type_button(): void
    {
        // Regresi: tombol navigasi kalender TANPA type="button" akan
        // submit form induk (default type submit) dan menutup popup.
        $js = file_get_contents(base_path('resources/js/ui-primitives.js'));
        foreach (['data-cal-prev', 'data-cal-next', 'data-cal-month', 'data-cal-year', 'data-cal-today', 'data-cal-clear', 'data-cal-day', 'data-cal-pickmonth', 'data-cal-pickyear'] as $marker) {
            $pattern = '/<button[^>]*' . preg_quote($marker, '/') . '[^>]*>/';
            preg_match_all($pattern, $js, $m);
            $this->assertNotEmpty($m[0], "Tombol {$marker} harus ada di ui-primitives.js.");
            foreach ($m[0] as $tag) {
                $this->assertStringContainsString('type="button"', $tag, "Tombol {$marker} wajib type=\"button\".");
            }
        }
    }

    public function test_escape_coordination_between_popover_and_modal(): void
    {
        // Regresi: Escape dengan kalender terbuka di dalam modal TIDAK
        // boleh menutup modal (koordinasi antar handler).
        $js = file_get_contents(base_path('resources/js/app.interactions.js'));
        $this->assertStringContainsString('data-ctl-popover-open', $js, 'Modal Escape handler wajib menghormati popover yang terbuka.');
    }

    public function test_primitive_registry_exists(): void
    {
        foreach (['select', 'date-picker', 'time-picker', 'datetime-picker', 'file-upload', 'checkbox', 'radio', 'switch', 'modal', 'tabs', 'tooltip', 'table', 'badge'] as $component) {
            $this->assertFileExists(
                base_path("resources/views/components/ui/{$component}.blade.php"),
                "Primitif x-ui.{$component} wajib ada."
            );
        }
        $this->assertFileExists(base_path('resources/js/ui-primitives.js'));
        $this->assertFileExists(base_path('docs/ui-primitives.md'));
    }
}
