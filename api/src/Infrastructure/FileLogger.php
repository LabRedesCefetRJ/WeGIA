<?php

namespace api\Infrastructure;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use RuntimeException;
use Stringable;
use Throwable;

class FileLogger extends AbstractLogger
{
    private const LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    public function __construct(private readonly string $logFile)
    {
        if ($logFile === '') {
            throw new InvalidArgumentException('The log file path cannot be empty.');
        }

        $directory = dirname($logFile);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create log directory "%s".', $directory));
        }
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (!is_string($level) || !in_array($level, self::LEVELS, true)) {
            $invalidLevel = is_scalar($level) || $level instanceof Stringable
                ? (string) $level
                : get_debug_type($level);

            throw new InvalidArgumentException(sprintf('Invalid log level "%s".', $invalidLevel));
        }

        $logData = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => (string) $message,
        ];

        if (($context['exception'] ?? null) instanceof Throwable) {
            $logData['exception'] = $this->formatException($context['exception']);
        }

        $record = json_encode(
            $logData,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($record === false) {
            throw new RuntimeException('Unable to encode the log record.');
        }

        $this->append($record . PHP_EOL);
    }

    private function formatException(Throwable $exception): array
    {
        $details = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        if ($exception->getPrevious() !== null) {
            $details['previous'] = $this->formatException($exception->getPrevious());
        }

        return $details;
    }

    private function append(string $record): void
    {
        $stream = @fopen($this->logFile, 'ab');

        if ($stream === false) {
            throw new RuntimeException(sprintf('Unable to open log file "%s" for writing.', $this->logFile));
        }

        $locked = false;

        try {
            if (!flock($stream, LOCK_EX)) {
                throw new RuntimeException(sprintf('Unable to lock log file "%s".', $this->logFile));
            }

            $locked = true;
            $length = strlen($record);
            $written = 0;

            while ($written < $length) {
                $bytes = fwrite($stream, substr($record, $written));

                if ($bytes === false || $bytes === 0) {
                    throw new RuntimeException(sprintf('Unable to write to log file "%s".', $this->logFile));
                }

                $written += $bytes;
            }
        } finally {
            if ($locked) {
                flock($stream, LOCK_UN);
            }

            fclose($stream);
        }
    }
}
