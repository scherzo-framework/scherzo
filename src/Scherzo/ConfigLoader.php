<?php
/**
 * This file is part of the Scherzo application framework.
 *
 * @link      https://github.com/paulbloomfield-uk/scherzo
 * @license   [MIT](https://github.com/paulbloomfield-uk/scherzo/blob/master/LICENSE).
 * @copyright Copyright © 2017 [Paul Bloomfield](https://github.com/paulbloomfield-uk).
**/

namespace Scherzo;

use Symfony\Component\Yaml\Yaml;

/**
 * Configuration loader/reader.
 *
 * @package Scherzo
**/
class ConfigLoader implements \ArrayAccess {

    protected $data = [];
    protected $methods = [
        'yml' => 'loadYaml',
        'yaml' => 'loadYaml',
        'json' => 'loadJson',
    ];

    /**
     * Constructor.
     *
     * @param  array Configuration to load.
     * @param  bool  Flag for nested keys in defaults.
     */
    public function __construct(array $defaults = [], $isNested = false) {
        if ($isNested) {
            $this->setNested($defaults);
        } else {
            $this->data = $defaults;
        }
    }

    // ## ArrayAccess Implementation. --------------------------------------------------------------
    /**
     * Implement ArrayAccess.
     *
     * @param  integer|string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool {
        return $this->has($offset);
    }

    /**
     * Implement ArrayAccess.
     *
     * @param  integer|string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->get($offset, null, true);
    }

    /**
     * Implement ArrayAccess.
     *
     * @param  integer|string $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) : void {
        $this->set($offset, $value);
    }

    /**
     * Implement ArrayAccess.
     *
     * @param  integer|string $offset
     * @return void
     */
    public function offsetUnset($offset) : void {
        $this->unset($offset);
    }

    // ## Parameter Bag-like implementation --------------------------------------------------------
    /**
     * Get all the data.
     *
     * @return array The data.
     */
    public function all() {
        return $this->data;
    }

    /**
     * Get a value for a key.
     *
     * @param  integer|string $offset
     * @return mixed
     */
    public function get(string $key = null, $default = null, $throw = false) {
        if ($key === null) {
            return $this->all();
        }
        if ($throw) {
            return $this->data[$key];
        }
        if ($this->has($key)) {
            return $this->data[$key];
        }
        return $default;
    }

    /**
     * Check if a key exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has(string $key) : bool {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set a value for a key.
     *
     * @param  integer|string $offset
     * @param  mixed $value
     * @return void
     */
    public function set(string $key, $value) : void {
        $this->data[$key] = $value;
    }

    /**
     * Unset the value for a key.
     *
     * @param  integer|string $offset
     * @return void
     */
    public function unset(string $key) : void {
        unset($this->data[$key]);
    }

    // ## Implement nested keys --------------------------------------------------------------------
    /**
     * Set data using a nested key.
     *
     * @param  bool|string  Separator.
     * @param  array        Source data (defaults to $_ENV).
     * @return ConfigLoader Chainable.
     */
    public function setNested(string $nestedKey, $value, string $separator = '.') : ConfigLoader {
        $keys = explode($separator, $nestedKey);
        $current = &$this->data;
        while ($key = array_shift($keys)) {
            if (count($keys)) {
                // Further nesting so make sure we have an array to nest in.
                if (!is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            } else {
                $current[$key] = $value;
            }
        }
        return $this;
    }

    // ## Loaders ----------------------------------------------------------------------------------
    /**
     * Load data from an array.
     *
     * @param  array Configuration to load.
     * @return ConfigLoader  Chainable.
     */
    public function loadArray(array $config) : ConfigLoader {
        $this->data = array_replace_recursive($this->data, $config);
        return $this;
    }

    /**
     * Load data from an array.
     *
     * @param  array Configuration to load.
     * @return ConfigLoader  Chainable.
     */
    public function loadEach(array $configs) : ConfigLoader {
        array_walk($configs, [$this, 'loadFileOrArray']);
        return $this;
    }

    /**
     * Load data from $_ENV.
     *
     * @param  bool|string  Separator.
     * @param  array        Source data (defaults to $_ENV).
     * @return ConfigLoader Chainable.
     */
    public function loadEnv(array $source = null, $separator = null) : ConfigLoader {
        if ($source === null) {
            // Use $_ENV and default to '_' to nest keys.
            $separator = $separator ?? '_';
            $source = $_ENV;
        } else {
            // Default to '.' to nest keys.
            $separator = $separator ?? '.';
        }
        array_walk($source, function ($value, $key) {
            $this->setNested($key, $value, $separator);
        });
        return $this;
    }

    /**
     * Load data from a file.
     *
     * @param  string       Path to file.
     * @param  string       (null) Name of variable containing the data if not returned by the file.
     * @return ConfigLoader Chainable.
     */
    public function loadFile(string $filename) : ConfigLoader {
        $type = pathinfo($filename, PATHINFO_EXTENSION);
        if ($type === 'php') {
            return $this->loadPhpFile($filename);
        }
        $string = file_get_contents($filename);
        $method = $this->methods[$type];
        return call_user_func([$this, $method], $string);
    }

    /**
     * Load data from an array.
     *
     * @param  array Configuration to load.
     * @return ConfigLoader  Chainable.
     */
    public function loadFileOrArray($filenameOrArray) {
        if (is_string($filenameOrArray)) {
            return $this->loadFile($filenameOrArray);
        }
        if (is_array($filenameOrArray)) {
            return $this->loadArray($filenameOrArray);
        }
        return $this;
    }

    /**
     * Load data from a JSON string
     *
     * @param  string       The JSON data
     * @return ConfigLoader Chainable.
     */
    public function loadJson(string $json, int $options = null) : ConfigLoader {
        $settings = $options ?? JSON_BIGINT_AS_STRING | JSON_OBJECT_AS_ARRAY | JSON_THROW_ON_ERROR;
        $assoc = ($settings & JSON_OBJECT_AS_ARRAY) > 0;
        $data = json_decode($json, $assoc, $settings);
        return $this->loadArray($data);
    }

    /**
     * Load data from an array returned by a PHP file.
     *
     * @param  string       Path to file.
     * @param  string       (null) Name of variable containing the data if not returned by the file.
     * @return ConfigLoader Chainable.
     */
    public function loadPhpFile(string $filename, string $variable = null) : ConfigLoader {
        if ($variable === null) {
            $config = require($filename);
        } else {
            require($filename);
            $config = $$variable;
        }
        return $this->loadArray($config);
    }

    /**
     * Load data from a Yaml string.
     *
     * @param  string       The Yaml data
     * @return ConfigLoader Chainable.
     */
    public function loadYaml(string $yaml) : ConfigLoader {
        $data = Yaml::parse($yaml);
        return $this->loadArray($data);
    }

}
