<?php

/**
 * HTTP Response.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2019-2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

declare(strict_types=1);

namespace Scherzo;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{
    protected $json;

    public const CONTENT_TYPE_JSON = 'application/json';

    public function setData(array $data): static
    {
        try {
            $json = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $this->setContent($json);
            $this->headers->set('Content-Type', static::CONTENT_TYPE_JSON);
            return $this;
        } catch (\Throwable $e) {
            throw($e);
            $this->setContent($e->getMessage());
            $this->setStatusCode(500);
            return $this;
        }
    }
}
