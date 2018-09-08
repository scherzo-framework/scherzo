<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

trait ServiceTrait {

    /** @var boolean Set to false to avoid loading settings from config. */
    protected $autoConfig = true;

    /** @var Container Dependencies container. */
    protected $container;

    /** @var array Default settings. */
    protected $defaults = [];

    /** @var string The id of this service in the container. */
    protected $name;

    /** @var array Current settings. */
    protected $settings = [];

    /**
     * Constructor.
     *
     * @param  string  Dependencies container.
     * @param  string  The id of this service in the container.
    **/
    public function __construct($container = null, $name = null) {
        $this->container = $container;
        $this->name = $name;
        if ($this->autoConfig) {
            $this->loadSettings($this->name);
        };
        $this->initialize();
    }

    /**
     * Initialise the service.
     *
     * Called by the constructor and to be used in preference to overloading the constructor.
    **/
    protected function initialize() : void {
    }

    /**
     * Load settings from config.
    **/
    protected function loadSettings(string $name = null) : void {
        if (!empty($name)) {
            try {
                $this->settings = array_merge_recursive($this->defaults,
                    $this->container->config->get($name) ?? []);
            } catch (\Throwable $e) {
                throw $e;
            }
        }
        $this->settings = $this->defaults;
    }
}
