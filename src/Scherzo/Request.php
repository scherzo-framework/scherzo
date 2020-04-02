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
            'Property :name not set on this request instance', [
                ':name' => $name,
        ]]);
    }

    /**
     * Magic method to set request variables.
     *
     * @param string $name  Variable name.
     * @param mixed  $value Associative array values will be converted to parameter bags.
     */
    public function __set(string $name, $value): void {
        if (is_array($value) && (array_values($value) !== $value)) {
            $this->attributes->set($name, new ParameterBag($value));
        } else {
            $this->attributes->set($name, $value);
        }
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
