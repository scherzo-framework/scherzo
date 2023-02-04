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

class Exception extends \Exception
{
    protected $title = 'Application error';

    /**
     * Get the title.
     *
     * @return string The (human friendly) title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title (chainable).
     *
     * @param string $title the title to display for this error.
     * @return HttpException Returns `$this` for chaining.
     */
    public function setTitle(string $title): self
    {
        try {
            $this->title = strval($title);
        } catch (\Throwable $e) {
            // If not a valid string leave the title untouched.
        }
        return $this;
    }
}
