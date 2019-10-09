<?php

/**
 * HTTP Request.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2019 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\RequestInterface;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\ParameterBag;

class Request extends HttpFoundation\Request implements RequestInterface {

    public function isProduction() {
        return isset($_ENV['PHP_ENV']) && $_ENV['PHP_ENV'] === 'production';
    }

    public function params($name = null) {
        if ($name === null) {
            return $this->attributes->get('params')->all();
        }
        return $this->attributes->get('params')->get($name);
    }

    public function setParams($params) {
        $this->attributes->set('params', new ParameterBag($params));
    }
}
