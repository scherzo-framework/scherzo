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
 * Debug dumper.
**/
class Debug extends Service
{
    protected $defaults = array(
        'sourceBefore' => 5,
        'sourceLength' => 11,
    );

    protected function afterConstructor()
    {
        require_once(__DIR__.'/../../../vendor/tracy/src/tracy.php');
    }

    public function dump($var, $return = null) {
        return \Tracy\Debugger::dump($var, $return);
    }

    public function dumpException($e)
    {
        $file = $e->getFile();
        $line = $e->getLine();

        $vars = array(
            'class'     => get_class($e),
            'message'   => $e->getMessage(),
            'line'      => $line,
            'fileName'  => basename($file),
            'file'      => $file,
            'stack'     => $this->trace($e->getTrace()),
            'included'  => $this->dump(get_included_files(), true),
            'services'  => $this->dump($depends->getLoaded(), true),
            'extract'   => $this->source($file, $line),
        );
        return $vars;
    }

    public function getServicesPanel()
    {
        return array(
            'tab' => 'Loaded services',
            'panel' => $this->dump($this->depends->getLoaded(), true),
            'bottom' => true,
        );
    }

    public function showErrorPage($e)
    {
        \Tracy\Debugger::$time = time();
        $errorPage = \Tracy\Debugger::getBlueScreen();
        $errorPage->addPanel(array($this, 'getServicesPanel'));
        $errorPage->render($e);
    }

    /**
     * Get a source file extract.
     *
     * @param  string   $file     The path to the source file.
     * @param  integer  $line     The line to highlight.
     * @param  array    $options  The line to highlight.
     * @return string   HTML to use inside a <pre>
    **/
    public static function source($file, $line, $options = array())
    {
        $settings = array_merge(static::$defaults, $options);
        $source = file_get_contents($file);
        $lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $source));
        $l = max($line - $settings['sourceBefore'] - 1, 0);
        $last = min($l + $settings['sourceLength'], count($lines));
        $extract = '';
        while ($l < $last)
        {
            $code = empty($lines[$l]) ? ' ' : $lines[$l];
            if ($l == $line -1) {
                $extract .= '<div class="highlight">'.$code.'</div>';
            } else {
                $extract .= '<div>'.$code.'</div>';
            }
            $l++;
        }
        return $extract;
    }

    public function trace($stack) {
        $out = array();
        $default = array(
            'function' => null,
            'line'     => null,
            'file'     => null,
            'class'    => null,
            'object'   => null,
            'type'     => null,
            'args'     => null,
        );
        foreach ($stack as $key => $trace) {
            if (isset($trace['file'])) {
                $trace['extract'] = static::source($trace['file'], $trace['line']);
            } else {
                $trace['extract'] = null;
            }
            $trace['dumpArgs'] = static::dump($trace['args'], true);
            $stack[$key] = array_merge($default, $trace);
        }
        return $stack;
    }

}
