<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/scherzo-framework/scherzo
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017-18 [Paul Bloomfield](https://github.com/scherzo-framework).
**/
namespace Scherzo\Container;

use Scherzo\Container\ContainerException;

/**
 * Thrown by a Container if an entry is not found.
**/
class ContainerNotFoundException extends ContainerException 
    implements \Psr\Container\NotFoundExceptionInterface {}
