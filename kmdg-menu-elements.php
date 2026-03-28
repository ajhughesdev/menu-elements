<?php
/*
Plugin Name: KMDG Menu Elements
Plugin URI: http://KMDG.com
Description: Allows you to add column markers to menus, and provides a custom nav walker for displaying them. For full support use the walker KMDG\MenuElements\Walker in your theme. This is automatically used if no other walker is specified.
Author: Jake Finley
Version: 1.1.0
Author URI: http://kmdg.com
*/

include('aesir/bootstrap.php');

spl_autoload_register(function ($class) {
    $prefix = "KMDG\\MenuElements\\";

    $base_dir = __DIR__ . "/classes";

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $class_file = $base_dir . '/' . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($class_file)) {
        require $class_file;
    }
});

/**
 * @return \KMDG\MenuElements\Plugin
 */
function MenuElements() {
    return KMDG\MenuElements\Plugin::getInstance();
}

MenuElements();