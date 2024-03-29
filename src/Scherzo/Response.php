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
    protected $data;

    public const CONTENT_TYPE_JSON = 'application/json';

    public function setData(array $data): static
    {
        // Only set the data now, the content is generated in `$this->prepare`.
        $this->data = $data;

        return $this;
    }

    public function prepare(Request $request): static
    {
        if ($this->data !== null) {
            $this->prepareDataResponse($request);
        }
        return parent::prepare($request);
    }

    protected function prepareDataResponse(Request $request): void
    {
        $this->headers->set('Content-Type', static::CONTENT_TYPE_JSON);
        $json = json_encode($this->data);
        $this->setContent($json);
    }
}
