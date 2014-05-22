<?php
/**
 * Local settings for a Scherzo application.
 *
 * @link      http://github.com/scherzo-framework/scherzo/
 * @copyright Copyright © 2014 MrAnchovy http://www.mranchovy.com/
 * @license   MIT
**/

class Local extends Scherzo\Core\Local
{
    public $coreApplicationNamespace = 'ScherzoDemo';

    /**
     * Any settings that need to be done at run-time can be done here.
    **/
    public function afterConstructor()
    {
        $this->coreApplicationDirectory = __DIR__.'/../examples/demo/';
    }
}
