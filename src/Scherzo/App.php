<?php

/**
 * Scherzo application.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2019 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Router;
use Scherzo\Request;
use Scherzo\Response;
use Scherzo\RequestInterface;

class App extends Router {

    public function __construct(Container $container = null) {
        $this->container = $container === null ? new \StdClass() : $container;
        parent::__construct();
    }

    public function __invoke(RequestInterface $req = null, $res = null) {
        if ($req === null) {
            $req = Request::createFromGlobals();
        }
        if ($res === null) {
            $res = new Response;
        }
        parent::__invoke($req, $res);
    }
}
