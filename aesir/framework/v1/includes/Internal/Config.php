<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 9/18/2018
 * Time: 10:48 AM
 */

namespace Aesir\v1\Internal;


use Aesir\v1\Exceptions\AesirException;
use Aesir\v1\Traits\ArrayMerge;

class Config
{
    use ArrayMerge;

    protected $data = [
        // Dependencies
        'dependencies/file'          => "dependencies.config.php",
        'dependencies/autowiring'   => true,

        // Routes
        'routes/file'  => "routes.config.php",
    ];

    const READ_ONLY = [
        'dependencies',
        'dependencies/autowiring'
    ];

    public function __construct($file)
    {
        if(file_exists($file) && is_file($file) && is_readable($file)) {
            $data = include($file);
            $this->data = $this->merge($this->data, $data);
        } else {
            throw new AesirException("Config file doesn't exist or isn't able to be read.");
        }
    }

    public function get($name, $default = null) {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    public function set($name, $value) {
        if(in_array($name, static::READ_ONLY)) {
            throw new AesirException("Cannot set read-only config property [$name].");
        }

        $this->data[$name] = $value;
    }
}