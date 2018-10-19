<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo\Service;

use Scherzo\ServiceTrait;

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
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
        throw $e;
    }
}
