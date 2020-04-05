<?php declare(strict_types=1);

namespace App;

use Scherzo\HttpException;

class Logger {
    protected $entries = [];

    function log(string $type, string $message, array $info = null) : self {
        $this->entries[] = $info === null ? [$type, $message] : [$type, $message, $info];
        return $this;
    }

    function getLog() : array {
        return $this->entries;
    }

    public function errorLoggingMiddleware(\Throwable $err, $req, $res): void {
        if (is_a($err, HttpException::class)) {
            // We don't need to log HttpExceptions.
            throw $err;
        }
        $this->log('debug', 'Unexpected exception', [
            'message' => $err->getMessage(),
            'string' => (string)$err,
        ]);
        throw new HttpException(500, $err->getMessage(), $err);
    }
}
