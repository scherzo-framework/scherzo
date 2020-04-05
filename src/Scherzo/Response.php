<?php declare(strict_types=1);

/**
 * An HTTP response.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2020 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [ISC](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Symfony\Component\HttpFoundation;
use Symfony\Component\HttpFoundation\ParameterBag;

class Response extends HttpFoundation\Response {
    /** @var ParameterBag JSON content. */

    protected $json = null;

    public function __get($name) {
        if ($name === 'json') {
            if ($this->json === null) {
                $this->json = new ParameterBag;
            }
            return $this->json;
        }
    }

    public function __construct(?string $content = '', int $status = 200, array $headers = []) {
        $this->json = new ParameterBag;
        parent::__construct($content, $status, $headers);
    }

    public function deprecated_addJson(string $attribute, $keyOrValue, $assocValue = null) : self {

        $current = $this->getJson($attribute);

        if ($current === null) {
            $this->setJson(
                $attribute,
                $assocValue === null ? $keyOrValue : [$keyOrValue => $assocValue]
            );
            return $this;
        }

        if ($assocValue === null) {
            // Add an unkeyed value to an assoc array!
            $current[] = $keyOrValue;
        } else {
            $current[$keyOrValue] = $assocValue;
        }
        $this->jsonParameterBag->set($attribute, $current);
        return $this;
    }

    public function deprecated_getJson(string $attribute = null) {
        if ($this->jsonParameterBag === null) {
            return null;
        }

        if ($attribute === null) {
            return $this->jsonParameterBag->all();
        }

        return $this->jsonParameterBag->has($attribute) ? $this->jsonParameterBag->get($attribute) : null;
    }

    public function deprecated_setData($value = null) : self {
        return $this->setJson('data', $value);
    }

    public function deprecated_setError($value) : self {
        return $this->setJson('error', $value);
    }

    public function deprecated_setJson(string $attribute = null, $value = null) : self {
        if ($attribute === null) {
            // Clear all the json!
            $this->jsonParameterBag = null;
            return $this;
        }

        if ($value === null) {
            // Clear this json attribute.
            if ($this->jsonParameterBag !== null && $this->jsonParameterBag->has($attribute)) {
                $this->jsonParameterBag->remove($attribute);
            }
            return $this;
        }

        if ($this->jsonParameterBag === null) {
            $this->jsonParameterBag = new ParameterBag;
        }
        $this->jsonParameterBag->set($attribute, $value);
        return $this;
    }

    public function prepare($req) {
        if (headers_sent()) {
            return;
        }
        // Handle JSON content.
        $jsonData = $this->json->all();
        if (!empty($jsonData)) {
            $this->setContent(json_encode($jsonData));
            $this->headers->set('Content-Type', 'application/json');
        }
        parent::prepare($req);
    }
}
