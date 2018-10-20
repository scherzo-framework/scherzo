<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

/**
 * Thrown by a Router if the HTTP method is not allowed for the route.
**/
class HttpMethodNotAllowedException extends HttpException {
    /** @var array */
    protected $allowedMethods = [];

    protected $status = 405;

    public function getAllowedMethods() : array {
        return $this->allowedMethods;
    }

    public function setAllowedMethods(array $methods) : self {
        $this->allowedMethods = $methods;
        return $this;
    }
}
