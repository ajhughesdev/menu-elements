<?php

namespace Aesir\v1\Internal;

use Aesir\v1\Aesir;
use Aesir\v1\Components\DependencyContainer;
use Aesir\v1\Components\Request;
use Aesir\v1\Components\Router;
use DI\ContainerBuilder;

// Only run setup once
if(class_exists("Aesir\\v1\\Aesir", false)) return;

$aesir = Aesir::getInstance();
$router = Router::getInstance();
$request = Request::getInstance();

// Setup theme settings

$theme = wp_get_theme();
$themeSettings = [];

do {

    $path = trailingslashit($theme->get_stylesheet_directory());
    $themeConfigFile = "{$path}aesir.config.php";

    if(file_exists($themeConfigFile)) {
        $name = $theme->get_stylesheet();

        $defaults = [
            'type'          => 'theme',
            'theme'         => $theme,
            'paths'         => [
                'base'          => $path,
                'dependencies'  => "{$path}dependencies.config.php",
                'routes'  => "{$path}routes.config.php",
            ]
        ];

        $custom = include($themeConfigFile);

        $themeSettings[$name] = array_merge($defaults, $custom);

    }
} while($theme = $theme->parent());

// Organize themes in parent -> child order
$themeSettings = array_reverse($themeSettings);


// Setup plugin settings
$plugginSettings = [];

foreach(wp_get_active_and_valid_plugins() as $plugin) {
    $path = trailingslashit(dirname($plugin));
    $pluginConfigFile = "{$path}aesir.config.php";

    if(file_exists($pluginConfigFile)) {
        $name = basename($path);

        $defaults = [
            'type'          => 'plugin',
            'plugin'        => $name,
            'paths'         => [
                'base'          => $path,
                'dependencies'  => "{$path}dependencies.config.php",
                'routes'  => "{$path}routes.config.php",
            ]
        ];

        $custom = include($pluginConfigFile);

        $plugginSettings[$name] = array_merge($defaults, $custom);
    }
}

$builder = new ContainerBuilder(DependencyContainer::class);

$builder->useAutowiring(true);

// Core dependency definitions
$builder->addDefinitions(include('config/dependencies.php'));

// Combine Theme & Plugin based settings
$settings = $plugginSettings + $themeSettings;

foreach($settings as $name => $config) {
    if(file_exists($config['paths']['dependencies'])) {
        call_user_func(function() use ($builder, $config) {
            $builder->addDefinitions(include($config['paths']['dependencies']));
        });
    }

    if(file_exists($config['paths']['routes'])) {
        call_user_func(function() use ($config) {
            include($config['paths']['routes']);
        });
    }
}

$aesir->setup($settings);

$router->setupContainer($builder->build());

$router->listen($request);

