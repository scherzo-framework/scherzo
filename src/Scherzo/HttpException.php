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

class HttpException extends \Exception
{
    protected int $statusCode = 500;

    protected array $allowedMethods = [];

    protected ?string $title = null;

    protected ?string $id = null;

    protected array $info = [];

    protected array $headers = [];

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
     * Set the allowed methods following 405 Method Not Allowed (chainable).
     *
     * @param array<int, string> $methods A list of allowed methods.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setAllowedMethods(array $methods): static
    {
        $this->allowedMethods = $methods;
        return $this;
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
    public function getHeaders(): array
    {
        return $this->headers;
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
     * Set information (chainable).
     *
     * @param string $key A key.
     * @param string $value A value.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setInfo(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->info = $key;
        } else {
            $this->info[$key] = $value;
        }
        return $this;
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
     * Set the HTTP status code (chainable).
     *
     * If the code provided is not in the range 400-599 it will be ignored.
     *
     * @param int $code An HTTP error status code.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setStatusCode(int $code): static
    {
        if ($code >= 400 && $code <= 599) {
            $this->statusCode = $code;
        }
        return $this;
    }

    /**
     * Get the title.
     *
     * @return string The (human friendly) title.
     */
    public function getTitle(): string
    {
        if ($this->title === null) {
            $this->title = Response::$statusTexts[$this->getStatusCode()];
        }
        return $this->title;
    }

    /**
     * Set the title (chainable).
     *
     * @param string $title the title to display for this error.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setTitle(string $title): static
    {
        try {
            $this->title = strval($title);
        } catch (\Throwable $e) {
            // If not a valid string leave the title untouched.
        }
        return $this;
    }
}
