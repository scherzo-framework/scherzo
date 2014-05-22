<?php
/**
 * This file is part of the Scherzo PHP application framework.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

namespace Scherzo\Core;

use Exception;

/**
 * Handle exceptions, errors and shutdown.
 *
 * Normal execution
 *     - code calls shutdown()
 *     - set status to CONTROLLED_START
 *     - clean up and call hooks
 *     - set status to CONTROLLED_COMPLETE
 *     - call exit()
 *     - PHP calls shutdownHandler()
 *     - shutdownHandler checks CONTROLLED_COMPLETE
 *     - shutdownHandler calls exit()
 *
 * Fatal error trapping
 *     - PHP calls shutdownHandler()
 *     - set status to FATAL_START
 *     - shutdownHandler creates an exception and invokes the error controller
 *     - shutdownHandler calls shutdown()
 *     - clean up and call hooks
 *     - set status to FATAL_COMPLETE
 *     - returns to shutdownHandler
 *     - call exit()
 *
 * @package  Scherzo\Core
**/
class Flow extends \Scherzo\Core\Service
{
    const CONTROLLED_START = 1;
    const CONTROLLED_COMPLETE = 2;
    const FATAL_START = 3;

    /**
     * Flag to indicate a controlled shutdown.
    **/
    protected $progress;

    /**
     * Set up error and exception handling.
     *
     * Because this class extends `Scherzo\Core\Service` this method is called
     * by the constructor.
    **/
    protected function afterConstructor() {
        error_reporting(-1);
        ini_set('display_errors', 0);
        set_error_handler(array($this, 'errorHandler'), -1);
        set_exception_handler(array($this, 'exceptionHandler'));
        register_shutdown_function(array($this, 'shutdownHandler'));
    }

    /**
     * Error handler.
     *
     * @param  integer  $level    The level of the error raised. 
     * @param  string   $message  The error message.
     * @param  string   $file     The filename that the error was raised in (optional).
     * @param  integer  $line     The line number the error was raised at (optional).
     * @param  array    $context  The active symbol table at the point the error occurred.
    **/
    public function errorHandler($level, $message, $file, $line, $context)
    {
        if (($level == E_WARNING) && isset($context['ignoreWarning'])) {
            // let the PHP error handler register for last_error
            return true;
        }
        $this->exceptionHandler(new \ErrorException($message, 0, $level, $file, $line));
    }

    /**
     * Exception handler.
     *
     * This is registered as the PHP exception handler and is also called by
     * `errorHandler` and `shutdownHandler` (after a fatal error) to deal with
     * exceptions they generate.
     *
     * @param  \Exception  $e  The exception to handle.
    **/
    public function exceptionHandler(Exception $e)
    {
        try {
            $this->displayException($e);
            $this->shutdown();

            throw new Exception('Should never get here');

        } catch (Exception $eee) {
            if (!headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: text/plain');
            }
            echo "Error in exception handler: " . (string)$eee;
        }
    }

    /**
     * Perform a controlled shutdown.
     *
     * @todo implement hooks for e.g. logging
     *
     * @package  Scherzo\Core
    **/
    public function shutdown($fatal = null)
    {
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        if ($fatal === true) {  // --- called by shutdownHandler after fatal error
            $this->runShutdownHooks();
            return;
        } else { // ------------------ normal shutdown
            $this->progress = self::CONTROLLED_START;
            $this->runShutdownHooks();
            $this->progress = self::CONTROLLED_COMPLETE;
            // PHP will invoke shutdownHandler
            exit(0);
        }
    }

    /**
     * Shutdown handler.
    **/
    public function shutdownHandler()
    {
        switch ($this->progress) {
            case self::CONTROLLED_COMPLETE :
                // normal shutdown
                exit(0);
            case null :
                // fatal error
                try {
                    $this->progress = self::FATAL_START;
                    if ($error = error_get_last()) {
                        $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
                    } else {
                        $e = new Exception('Fatal error trapped but not reported by error_get_last');
                    }
                    $this->displayException($e);
                    $this->shutdown();
                    exit(1);
                } catch (Exception $e) {
                    $this->shutdownError('exception during fatal error handling');
                    exit(1);
                }
            case self::CONTROLLED_START :
                $this->shutdownError('fatal error during normal shutdown');
                exit(1);
            case self::FATAL_START :
                $this->shutdownError('fatal error during controlled shutdown after previous fatal error');
                exit(1);
            default :
                $this->shutdownError("unknown progress status [$this->progress] in shutdown handler");
                exit(1);
        }
    }

    protected function displayException(Exception $e)
    {
        try {
            $debug = $this->depends->debug->showErrorPage($e);
        } catch (Exception $ee) {
            $this->shutdownError('Exception "'.$ee->getMessage().'" trying to display debug dump');
        }
    }

    protected function runShutdownHooks()
    {
        // foreach ($this->shutdownHooks as hook) {
            try {
                // invoke hook
            } catch (Exception $e) {
                // log error in hook
            }
        // } end foreach
    }

    /**
     * Deal with a shutdown error.
     *
     * @todo need to log this somehow
    **/
    protected function shutdownError($message)
    {
        if (!headers_sent()) {

        }
        if (!$this->depends->local->isProduction()) {
            echo "Shutdown error - $message.<br>\n";
        } else {
            echo "There has been an error.";
        }
    }
}
