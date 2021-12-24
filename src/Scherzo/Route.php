<?php

/**
 * A route with parameters matched by the router.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Container;
use Scherzo\Request;
use Scherzo\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class Route extends ParameterBag
{
    protected $callback;
    protected $c;

    public function __construct(Container $c, array $routeInfo)
    {
        // $this->params = new ParameterBag($routeInfo[1]);
        parent::__construct($routeInfo[1]);
        $this->c = $c;
        $this->callback = $routeInfo[0];
    }

    public function execute(Request $request): Response
    {
        [$class, $method] = $this->callback;
        $response = new Response();
        $data = (new $class($this->c))->$method($request);
        return $response->setData(['data' => $data]);
    }
}
