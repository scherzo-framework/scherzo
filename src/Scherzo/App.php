<?php declare(strict_types=1);

/**
 * Scherzo application.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Scherzo\Router;
use Scherzo\Request;
use Scherzo\Response;

class App extends Router {
    public function __invoke(Request $req = null, $res = null) {
        if ($req === null) {
            $req = Request::createFromGlobals();
        }
        if ($res === null) {
            $res = new Response;
        }
        parent::__invoke($req, $res);
    }
}
