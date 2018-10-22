<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo\Integrations;

use Scherzo\ServiceTrait;
use Whoops\Run as WhoopsRunner;
use Whoops\Handler\PrettyPageHandler;

/**
 * Use Whoops error handler.
 *
 * @package Scherzo
**/
class Whoops {

    use ServiceTrait;

    /**
     *
    **/
    public function handle($e) {
        $whoops = new WhoopsRunner;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->register();
        throw $e;
    }
}
