<?php

namespace app\Core\Utils;

class Logger
{
    public const CHANNEL_ACCESS   = 'access';
    public const CHANNEL_ACTIVITY = 'activity';
    public const CHANNEL_AUDIT    = 'audit';

    public static function write(string $channel, array $data): void
    {
        DUtil::isDir($channel);

        $line = json_encode([
            'ts'       => date('c'),
            'channel'  => $channel,
            'data'     => $data,
        ], JSON_UNESCAPED_SLASHES) . PHP_EOL;

        $file = $_SERVER['DOCUMENT_ROOT'] . "/log/{$channel}/log_" . date("j.n.Y") . ".log";
        file_put_contents($file, $line, FILE_APPEND);
    }
}