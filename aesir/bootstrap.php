<?php
/*
Plugin Name: Aesir Framework
Plugin URI: http://kmdg.com
Description: Aesir Framework for WordPress developers
Version: 1.0.0
Author: KMDG, Inc., Jake Finley
Author URI: http://kmdg.com
License: MIT
*/

namespace Aesir;

/**
 * Autoloader for V1
 */
spl_autoload_register(function ($class) {
    load('v1', $class);
});

if(!function_exists('Aesir\\load')) {
    /**
     * Loads classes from the Aesir framework of a given API version
     * corresponding to the framework's major version number.
     *
     * @param string $version Framework version, ie "v1", "v2", etc.
     * @param string $class Fully qualified class name to load
     */
    function load($version, $class) {
        $prefix = "Aesir\\{$version}\\";

        $base_dir = __DIR__ . "/framework/{$version}";

        require_once("{$base_dir}/vendor/autoload.php");
        //require_once("{$base_dir}/setup.php");
        require_once("{$base_dir}/includes/Facades.php");

        $len = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);

        $class_file = $base_dir . '/includes/' . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($class_file)) {
            require $class_file;
        }
    }
}
