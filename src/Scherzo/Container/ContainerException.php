<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/
namespace Scherzo\Container;

use Scherzo\Exception;

/**
 * Thrown by a Container if an entry exists but cannot be loaded.
**/
class ContainerException extends Exception
    implements \Psr\Container\ContainerExceptionInterface {}
