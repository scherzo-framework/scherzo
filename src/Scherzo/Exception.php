<?php declare(strict_types=1);

/**
 * Utility class for exceptions in Scherzo.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

class Exception extends \RuntimeException {
    protected $messageString = '';
    protected $messageVars = [];

    /**
     * Constructor.
     *
     * Allows creation of a translatable, HTML-safe error message.
     *
     * @param string  The error message.
     * @param array   The error message template with placeholders and an array of substitutions.
     */
    public function __construct($message = null, \Throwable $previous = null) {
        if (is_array($message)) {
            $this->messageString = $message[0];
            $this->messageVars = $message[1];
            $message = strtr($this->messageString, $this->messageVars);
        }
        parent::__construct($message, 0, $previous);
    }
}
