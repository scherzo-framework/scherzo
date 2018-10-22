<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo;

/**
 * Extend base class to provide easier creation and translation of error messages.
**/
class Exception extends \Exception {

    /**
     * Constructor.
     *
     * @param  array|string  $message   Error message or array [$message, $vars].
     * @param  int           $code      A PHP error code.
     * @param  Throwable     $previous  A previous exception if any.
    **/
    public function __construct($message = null, int $code = 0, \Throwable $previous = null) {
        if (is_array($message)) {
            try {
                $text = array_shift($message);
                if (isset($message[0])) {
                    // Deal with `throw new Exception([$message, [':var1' => $var1...]])`.
                    $message = strtr($text, $message[0]);
                } else {
                    // Deal with `throw new Exception([$message, ':var1' => $var1...])`.
                    $message = strtr($text, $message);
                }
            } catch (\Throwable $e) {
                $message = strtr('Could not construct message for a :class thrown in line :line of :file',
                    [':class' => static::class, ':line' => $this->getLine(), ':file' => $this->getFile()]);
            }
        }
        parent::__construct($message, $code, $previous);
    }
}
