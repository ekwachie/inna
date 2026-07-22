<?php

namespace app\Core\Utils;

class Logger
{
    public const CHANNEL_ACCESS   = 'access';
    public const CHANNEL_ACTIVITY = 'activity';
    public const CHANNEL_AUDIT    = 'audit';

    public static function write(string $channel, array $data): void
    {
        $channel = trim($channel);
        if ($channel === '' || !preg_match('/^[a-z0-9_-]+$/i', $channel)) {
            return;
        }

        $line = json_encode([
            'ts'       => date('c'),
            'channel'  => $channel,
            'data'     => $data,
        ], JSON_UNESCAPED_SLASHES) . PHP_EOL;

        $attempted = [];
        foreach (self::channelDirectories($channel, true) as $dir) {
            $file = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "log_" . date("j.n.Y") . ".log";
            $attempted[] = $file;
            if ((is_file($file) && !is_writable($file)) || (!is_file($file) && !is_writable($dir))) {
                continue;
            }
            if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false) {
                return;
            }
        }

        self::reportUnavailable($channel, 'no writable log path found: ' . implode(', ', $attempted));
    }

    public static function channelDirectories(string $channel, bool $create = false): array
    {
        $channel = trim($channel);
        if ($channel === '' || !preg_match('/^[a-z0-9_-]+$/i', $channel)) {
            return [];
        }

        $root = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
        if ($root === '') {
            $root = dirname(__DIR__, 3);
        }

        $baseDirs = [];
        $configured = trim((string)($_ENV['APP_LOG_DIR'] ?? getenv('APP_LOG_DIR') ?: ''));
        if ($configured !== '') {
            $baseDirs[] = $configured;
        }
        $baseDirs[] = $root . '/log';
        $baseDirs[] = dirname($root) . '/logs';
        $baseDirs[] = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/inna-log';

        $dirs = [];
        foreach (array_unique(array_filter($baseDirs)) as $baseDir) {
            $dir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $channel;
            if ($create && !is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (is_dir($dir)) {
                $dirs[] = $dir;
            }
        }

        return array_values(array_unique($dirs));
    }

    private static function reportUnavailable(string $channel, string $message): void
    {
        static $reported = [];
        if (isset($reported[$channel])) {
            return;
        }

        $reported[$channel] = true;
        error_log("Logger {$channel} unavailable: {$message}");
    }
}
