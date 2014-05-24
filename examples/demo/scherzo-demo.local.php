<?php
/**
 * Local settings for a Scherzo application.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

/**
 * Local settings for a Scherzo application.
**/
class Local extends Scherzo\Core\Local
{
    /** The namespace used by your application. */
    public $coreApplicationNamespace = 'ScherzoDemo';

    /** The path to your application relative to this file. */
    public $coreApplicationDirectory = '';

    /**
     * Any settings that need to be done at run-time can be done here.
    **/
    public function afterConstructor()
    {
    }
}
