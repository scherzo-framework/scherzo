<?php declare(strict_types=1);

/**
 * HTTP Exceptions are handled to return a response.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

class HttpException extends \RuntimeException {
    protected $statusCode;
    protected $info;

    public function __construct(int $statusCode = 500, string $message = null, \Throwable $previous = null) {
        if ($statusCode >= 400 && isset(Response::$statusTexts[$statusCode])) {
            $this->statusCode = $statusCode;
        } else {
            $this->statusCode = 500;
        }
        $code = Response::$statusTexts[$this->statusCode];
        $message = $message === null ? $code : $message;
        parent::__construct($message, 0, $previous);
        $this->$code = $code;
    }

    public function setCode(string $code) {
        $this->code = $code;
        return $this;
    }

    public function setInfo($info, $value = null) {
        if (is_array($info)) {
            $this->info = $info;
        } elseif (isset($this->info)) {
            $this->info[$info] = $value;
        } else {
            $this->info = [$info => $value];
        }
        return $this;
    }

    public function getInfo() {
        return $this->info;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
}
