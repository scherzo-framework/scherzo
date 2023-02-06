<?php

/**
 * A route with parameters matched by the router.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2021-22 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Container;
use Scherzo\HttpException;
use Scherzo\Request;
use Scherzo\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class Route extends ParameterBag
{
    protected const ERROR_TITLE = 'Internal error: invalid route';

    protected $callback;
    protected $c;

    public function __construct(Container $c, array $routeInfo)
    {
        // $this->params = new ParameterBag($routeInfo[1]);
        parent::__construct($routeInfo[1]);
        $this->c = $c;
        $this->callback = $routeInfo[0];
    }

    public function dispatch(Request $request, Response $response): array|string|null
    {
        try {
            [$class, $method] = $this->callback;
            $handler = new $class($this->c);
            $data = $handler->$method($request, $response);
        } catch (HttpException $e) {
            // HttpExceptions are OK.
            throw ($e);
        } catch (\Throwable $e) {
            if (gettype($class) !== 'string') {
                $type = gettype($class);
                throw (new HttpException("Class must be a string ($type provided)", 0, $e))
                    ->setTitle(static::ERROR_TITLE);
            }
            if (!class_exists($class)) {
                throw (new HttpException("Class '$class' does not exist", 0, $e))
                    ->setTitle(static::ERROR_TITLE);
            }
            if (!method_exists($class, $method)) {
                throw (new HttpException("Method '$method' does not exist in class '$class'", 0, $e))
                    ->setTitle(static::ERROR_TITLE);
            }
            // If we have got here the definition of the route is OK, there has
            // been an error in executing it.
            throw $e;
        }

        switch (gettype($data)) {
            case 'array': // JSON data.
            case 'string': // HTML.
                return $data;
        }
        return null;
    }
}
