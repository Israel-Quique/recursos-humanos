<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BiometricoAutoSyncService
{
    private const LAST_TRIGGER_CACHE_KEY = 'biometrico:auto-sync:last-trigger-at';
    private const LOCK_CACHE_KEY = 'biometrico:auto-sync:trigger-lock';

    public function triggerIfDue(): void
    {
        if (!config('biometrico.web_auto_sync_enabled', false)) {
            return;
        }

        if (app()->runningInConsole()) {
            return;
        }

        $lastTriggerAt = Cache::get(self::LAST_TRIGGER_CACHE_KEY);

        if (filled($lastTriggerAt) && now()->diffInMinutes($lastTriggerAt) < $this->intervalMinutes()) {
            return;
        }

        $lock = Cache::lock(self::LOCK_CACHE_KEY, 30);

        if (!$lock->get()) {
            return;
        }

        try {
            $lastTriggerAt = Cache::get(self::LAST_TRIGGER_CACHE_KEY);

            if (filled($lastTriggerAt) && now()->diffInMinutes($lastTriggerAt) < $this->intervalMinutes()) {
                return;
            }

            $this->triggerNow();
        } finally {
            optional($lock)->release();
        }
    }

    public function triggerNow(): void
    {
        $this->spawnBackgroundSync();
        Cache::put(self::LAST_TRIGGER_CACHE_KEY, now(), now()->addMinutes($this->intervalMinutes() + 5));
    }

    private function spawnBackgroundSync(): void
    {
        $artisan = base_path('artisan');
        $phpBinary = PHP_BINARY;
        $process = new Process([$phpBinary, $artisan, 'biometrico:sync'], base_path(), $this->processEnvironment());
        $process->setTimeout(null);
        $process->disableOutput();

        if (DIRECTORY_SEPARATOR === '\\') {
            $process->start();
        } else {
            $process->start();
        }

        Log::info('Auto sync biometrico disparado desde la aplicacion web.', [
            'php' => $phpBinary,
            'artisan' => $artisan,
            'interval_minutes' => $this->intervalMinutes(),
        ]);
    }

    private function intervalMinutes(): int
    {
        return max(1, (int) config('biometrico.auto_sync_minutes', 15));
    }

    private function processEnvironment(): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return [];
        }

        $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';
        $path = array_filter(array_unique([
            dirname(PHP_BINARY),
            base_path(),
            $systemRoot,
            $systemRoot . DIRECTORY_SEPARATOR . 'System32',
            $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'Wbem',
            $systemRoot . DIRECTORY_SEPARATOR . 'System32' . DIRECTORY_SEPARATOR . 'WindowsPowerShell' . DIRECTORY_SEPARATOR . 'v1.0',
            getenv('PATH') ?: '',
        ]));

        return [
            'PATH' => implode(PATH_SEPARATOR, $path),
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => getenv('WINDIR') ?: $systemRoot,
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
        ];
    }
}
