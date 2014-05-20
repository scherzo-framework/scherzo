<?php

namespace Scherzo\Mock;

use Exception;

/**
 * Mock service.
**/
class Service extends \Scherzo\Core\Service
{
    public $afterConstructorHookHasRun;

    /**
     * Find out who we are
    **/
    public function getName()
    {
        return $this->name;
    }

    protected function afterConstructor()
    {
        $this->afterConstructorHookHasRun = true;
    }

}
