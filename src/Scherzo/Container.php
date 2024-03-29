<?php

/**
 * PSR-11 compliant container.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Scherzo\ContainerNotFoundException;

class Container extends PimpleContainer implements ContainerInterface
{
    protected $lazy = [];

    public function get(string $id): mixed
    {
        if ($this->offsetExists($id)) {
            return $this->offsetGet($id);
        }

        if (!array_key_exists($id, $this->lazy)) {
            $e = new ContainerNotFoundException("Key '$id' does not exist in this container");
            $e->setTitle('Container key not found');
            $e->setInfo('key', $id);
            throw $e;
        }

        $classOrFn = $this->lazy[$id];
        if (is_callable($classOrFn)) {
            $entry = call_user_func($this->lazy[$id], $this);
        } elseif (class_exists($classOrFn)) {
            $entry = new $classOrFn($this);
        } else {
            $entry = $classOrFn;
        }
        $this->offsetSet($id, $entry);
        return $entry;
    }

    public function set(string $id, mixed $value): static
    {
        $this->offsetSet($id, $value);
        return $this;
    }

    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }

    public function lazy(string $id, mixed $callback): static
    {
        $this->lazy[$id] = $callback;
        return $this;
    }
}
