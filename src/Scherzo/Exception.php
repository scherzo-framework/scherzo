<?php declare(strict_types=1);

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
            $message = strtr($message, $this->messageString, $this->messageVars);
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * Get an HTML safe version of the message.
     *
     * @return string The HTML safe message.
     */
    public function toHtmlString(): string {
        $htmlVars = [];
        foreach ($this->messageVars as $name->$raw) {
            $htmlVars[$name] = htmlspecialchars($value);
        }
        return strtr($this->messageString, $htmlVars);
    }
}
