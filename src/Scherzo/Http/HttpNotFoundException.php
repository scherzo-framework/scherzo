<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo\Http;

use Scherzo\Http\HttpException;

/**
 * Thrown by a Router if a matching route is not found.
**/
class HttpNotFoundException extends HttpException {
    protected $status = 404;
}
