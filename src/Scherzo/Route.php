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
use Scherzo\Exception;
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

    public function dispatch(Request $request): Response
    {
        try {
            [$class, $method] = $this->callback;
            $response = new Response();
            $handler = new $class($this->c);
            assert(is_callable([$handler, $method]));
        } catch (\Throwable $e) {
            if (gettype($class) !== 'string') {
                $type = gettype($class);
                $err = new Exception('Class must be a string (' . $type . ' provided)', 0, $e);
                throw $err->setTitle(self::ERROR_TITLE);
            }
            if (!class_exists($class)) {
                $err = new Exception('Class \'' . $class . '\' does not exist', 0, $e);
                throw $err->setTitle(self::ERROR_TITLE);
            }
            $err = new Exception($e->getMessage(), 0, $e);
            throw $err->setTitle(self::ERROR_TITLE);
        }

        $data = $handler->$method($request);
        return $response->setData(['data' => $data]);
    }
}
