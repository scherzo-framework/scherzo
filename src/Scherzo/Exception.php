<?php

declare(strict_types=1);

namespace Scherzo;

class Exception extends \RuntimeException {
    public function __construct($message, $code = null, \Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->code = $code;
    }
}
