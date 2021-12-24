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

class Container extends PimpleContainer implements ContainerInterface
{
    public function get(string $id)
    {
        return $this->offsetGet($id);
    }

    public function set(string $id, $value)
    {
        $this->offsetSet($id, $value);
        return $this;
    }

    public function has(string $id): bool
    {
        return $this->offsetExists($id);
    }
}
