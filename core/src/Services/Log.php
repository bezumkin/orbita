<?php

namespace App\Services;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Log
{
    protected const format = "[%datetime%] %message% %context% %extra%\n";
    protected const dateFormat = 'Y-m-d H:i:s';

    public static function getLogger(string $level, ?string $name = 'default'): ?Logger
    {
        if (!getenv('LOG_ENABLED')) {
            return null;
        }

        $dir = rtrim(getenv('LOG_DIR') ?: '/tmp', '/') . '/';
        $logger = new Logger($name);
        $handler = new RotatingFileHandler($dir . $level . '.log', 10, Logger::toMonologLevel($level), false, 0664);
        $handler->setFilenameFormat('{date}-{filename}', 'Y-m-d');
        $formatter = new LineFormatter(self::format, self::dateFormat, false, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    protected static function getLevel(): ?string
    {
        $level = strtolower(getenv('LOG_LEVEL') ?: '');

        return in_array($level, ['error', 'info', 'debug']) ? $level : null;
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger('error')?->error($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        if (in_array(self::getLevel(), ['info', 'debug'])) {
            self::getLogger('info')?->info($message, $context);
        }
    }

    public static function debug(string $message, array $context = []): void
    {
        if (self::getLevel() === 'debug') {
            self::getLogger('debug')?->debug($message, $context);
        }
    }
}