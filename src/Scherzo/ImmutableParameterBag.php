<?php

declare(strict_types=1);

namespace Scherzo;

use Symfony\Component\HttpFoundation\ParameterBag;

class ImmutableParameterBag extends ParameterBag
{
    protected const IMMUTABLE_MESSAGE = 'Route is immutable';
    protected const ERROR_TITLE = 'Internal error: invalid route';

    /**
     * Replaces the current parameters by a new set.
     */
    public function replace(array $parameters = [])
    {
        throw new \Exception(static::IMMUTABLE_MESSAGE);
    }

    /**
     * Adds parameters.
     */
    public function add(array $parameters = [])
    {
        throw new \Exception(static::IMMUTABLE_MESSAGE);
    }

    public function set(string $key, mixed $value)
    {
        throw new \Exception(static::IMMUTABLE_MESSAGE);
    }

    /**
     * Removes a parameter.
     */
    public function remove(string $key)
    {
        throw new \Exception(static::IMMUTABLE_MESSAGE);
    }
}
