<?php declare(strict_types=1);

namespace App;

class Logger {
    protected $entries = [];

    function log(string $type, string $message, array $info = null) : self {
        $this->entries[] = $info === null ? [$type, $message] : [$type, $message, $info];
        return $this;
    }

    function getLog() : array {
        return $this->entries;
    }
}
