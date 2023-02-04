<?php

/**
 * An exception for return to a user.
 *
 * @package   Scherzo
 * @link      https://github.com/scherzo-framework/scherzo
 * @copyright Copyright (c) 2014-2021 [Scherzo Framework](https://github.com/scherzo-framework)
 * @license   [MIT](https://github.com/scherzo-framework/scherzo/blob/master/LICENSE)
 *
 * Example usage:
 *
 * ```php
 * // Not Found.
 * throw (new HttpException('Could not find that resource'))
 *     ->setStatusCode(404);
 *
 * // User error.
 * throw (new HttpException('Invalid email address'))
 *     ->setStatusCode(400);
 *
 * // Method Not Allowed.
 * throw (new HttpException())
 *     ->setStatusCode(405)
 *     ->setAllowedMethods(['GET', 'PUT', 'DELETE']);
 *
 * // System error (defaults to 500).
 * throw new HttpException('Something went wrong');
 * ```
 * @package Scherzo
 */

declare(strict_types=1);

namespace Scherzo;

use Scherzo\Utils;

class HttpException extends \Exception
{
    protected $statusCode = 500;

    protected $allowedMethods = [];

    protected $title;

    protected $id;

    protected $info = [];

    /**
     * Get the allowed methods following 405 Method Not Allowed.
     *
     * @return array<int, string> A list of allowed methods.
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * Get a unique ID.
     *
     * @return string An v4 UUID.
     */
    public function getId(): string
    {
        if (!$this->id) {
            $this->id = Utils::getUuid();
        }
        return $this->id;
    }

    /**
     * Get any info.
     *
     * @return array Information.
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int The HTTP error status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the title.
     *
     * @return string The (human friendly) title.
     */
    public function getTitle(): string | null
    {
        return $this->title;
    }

    /**
     * Set the allowed methods following 405 Method Not Allowed (chainable).
     *
     * @param array<int, string> $methods A list of allowed methods.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setAllowedMethods(array $methods): self
    {
        $this->allowedMethods = $methods;
        return $this;
    }

    /**
     * Set the HTTP status code (chainable).
     *
     * If the code provided is not in the range 400-599 it will be ignored.
     *
     * @param int $code An HTTP error status code.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setStatusCode(int $code): self
    {
        if ($code >= 400 && $code <= 599) {
            $this->statusCode = $code;
        }
        return $this;
    }

    /**
     * Set information (chainable).
     *
     * @param string $key A key.
     * @param string $value A value.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setInfo(string $key, $value): self
    {
        $this->info[$key] = $value;
        return $this;
    }

    /**
     * Set the title (chainable).
     *
     * @param string $title the title to display for this error.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setTitle(string $title): self
    {
        $this->title = strval($title);
        return $this;
    }
}
