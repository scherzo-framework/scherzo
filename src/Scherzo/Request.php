<?php

/**
 * HTTP Request.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2019-2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Scherzo\Route;

class Request extends BaseRequest
{
    /** @var Route The route (and its parameters) for this request. */
    public ?Route $route = null;

    /** @var JSON body data. */
    public ?ParameterBag $data = null;

    public function getBearerToken($isEncoded = false): string|false
    {
        $authHeader = $this->headers->get('authorization', '');
        preg_match('@Bearer ([^\r\n]*)@', $authHeader, $matches);
        if (count($matches) === 0) {
            return false;
        }
        if (!$isEncoded) {
            return $matches[1];
        }
        return base64_decode($matches[1]);
    }
}
