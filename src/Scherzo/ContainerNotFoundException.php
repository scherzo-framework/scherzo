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

use Psr\Container\NotFoundExceptionInterface;
use Scherzo\HttpException;

class ContainerNotFoundException extends HttpException implements NotFoundExceptionInterface
{
}
