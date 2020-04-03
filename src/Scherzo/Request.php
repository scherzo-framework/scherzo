<?php declare(strict_types=1);

/**
 * HTTP Request.
 *
 * A Scherzo request is essentially a Symfony request with a bit of extra help.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2019 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 */

namespace Scherzo;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Scherzo\Exception;

class Request extends HttpFoundationRequest {
    /**
     * Magic method to get request variables.
     *
     * @param string  $name  Variable name.
     * @return mixed  The value of the name.
     * @throws Exception If the variable is not set.
     */
    public function __get(string $name) {
        if ($this->attributes->has($name)) {
            return $this->attributes->get($name);
        }
        throw new Exception([
            'Property :name is not set on this request', [
                ':name' => $name,
        ]]);
    }

    /**
     * Magic method to set request variables.
     *
     * @param string $name         Variable name to set.
     * @param mixed  $value        Value to set.
     */
    public function __set(string $name, $value): void {
        $this->set($name, $value);
    }

    /**
     * Set a variable on the request.
     *
     * If the $parameterBag argument is true a parameter bag will be created from an associative
     * array $value.
     *
     * @param string $name         Variable name to set.
     * @param mixed  $value        Value to set.
     * @param bool   $parameterBag Set to true to create a parameter bag from $value.
     * @return mixed The value, converted to a parameter bag if requested.
     */
    public function set(string $name, $value, bool $parameterBag = null) {
        // if (is_array($value) && (array_values($value) !== $value)) {
        if ($parameterBag) {
            $value = new ParameterBag($value);
        }
        $this->attributes->set($name, $value);
        return $value;
    }

    /**
     * Set the request content (for test purposes).
     *
     * @param  string  $content  Body content.
     * @return Request Chainable.
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the request content (for test purposes).
     *
     * @param  string  $content  Body content.
     * @return Request Chainable.
     */
    public function setJson(array $json): self {
        $this->headers->set('Content-Type', 'application/json');
        return $this->setContent(json_encode($json));
    }

    /**
     * Test for production mode request.
     *
     * @return bool true iff production mode.
     */
    public function isProduction() {
        return !isset($_ENV['PHP_ENV']) || $_ENV['PHP_ENV'] === 'production';
    }
}
