<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/

namespace Scherzo\Pipeline;

use Scherzo\Pipeline\PipelineException;

/**
 * Implement a pipeline of handlers.
**/
class Pipeline {

    /** @var mixed Dependencies container. */
    protected $container;

    /** @var array Stack of handlers. */
    protected $stack = [];

    /** @var int Stack pointer. */
    protected $stackPointer;

    /**
     * Constructor.
     *
     * @param  mixed  $container  Dependencies container.
    **/
    public function __construct($container = null) {
        $this->container = $container;
    }

    /**
     * Invoke the next item on the stack.
     *
     * @param  mixed  $request  Request to process.
     * @return mixed  Response to pass back.
    **/
    public function __invoke($next, $request) {
        return $this->next($next, $request);
    }

    /**
     * Invoke the next item on the stack.
     *
     * @param  mixed  $request  Request to process.
     * @return mixed  Response to pass back.
    **/
    protected function getNextHandler() {
        // start or increment the stack pointer
        if ($this->stackPointer === null) {
            $this->stackPointer = 0;
        } else {
            $this->stackPointer++;
        }

        // check the stack pointer is not past the end of the stack
        if ($this->stackPointer > count($this->stack) - 1) {
            throw new PipelineException('Cannot call past the end of the stack');
        }

        return $this->stack[$this->stackPointer];
    }

    /**
     * Invoke the next item on the stack.
     *
     * @param  mixed  $request  Request to process.
     * @return mixed  Response to pass back.
    **/
    protected function next($next, $request) {

        $handler = $this->getNextHandler();

        // deal with a closure
        if ($handler instanceof \Closure) {
            return $handler->call($this->container, $this, $request);
        }

        // deal with a service if we have a container to find it in
        if (is_array($handler) && is_string($handler[0]) && $this->container !== null) {
            $service = $handler[0];
            if ($this->container->has($service)) {
                $method = $handler[1];
                return $this->container->get($service)->$method($this, $request);
            }
        }

        if (is_callable($handler)) {
            return $handler($this, $request);
        }

        throw new PipelineException('Cannot handle the next item in the pipeline.');
    }

    /**
     * Push a handler onto the request processing stack.
     *
     * @param  mixed  $handler  A handler (specified as an array, a callable or a closure).
     * @return $this  Chainable.
    **/
    public function push($handler) : self {
        $this->stack[] = $handler;
        return $this;
    }

    /**
     * Push an array of handlers onto the request processing stack.
     *
     * @param  mixed  $handler  An array of handlers.
     * @return $this  Chainable.
    **/
    public function pushMultiple(array $handlers) : self {
        $this->stack = array_merge($this->stack, $handlers);
        return $this;
    }

    /**
     * Insert a handler at the current point on the request processing stack.
     *
     * @param  mixed  $handler  A handler (specified as an array, a callable or a closure).
     * @return $this  Chainable.
    **/
    public function insert($handler) {
        $position = $this->stackPointer === null ? 0 : $this->stackPointer + 1;
        $this->stack = array_splice($this->stack, $position, 0, [$handler]);
        return $this;
    }

    /**
     * Insert multiple handlers at the current point on the request processing stack.
     *
     * @param  mixed  $handlers  An array of handlers (each specified as an array,
     *                           a callable or a closure).
     * @return $this  Chainable.
    **/
    public function insertMultiple($handlers) {
        $position = $this->stackPointer === null ? 0 : $this->stackPointer + 1;
        $this->stack = array_splice($this->stack, $position, 0, $handlers);
        return $this;
    }

}
