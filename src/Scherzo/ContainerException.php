<?php declare(strict_types=1);

/**
 * Thrown by a Container if an entry exists but cannot be loaded.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Psr\Container\ContainerExceptionInterface;

class ContainerException
    extends \Exception
    implements ContainerExceptionInterface {}
