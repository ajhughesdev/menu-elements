<?php

namespace Aesir\v1;

use Aesir\v1\Components\Router;
use Aesir\v1\Exceptions\AesirException;
use Aesir\v1\Exceptions\MissingDependency;
use Aesir\v1\Internal\Config;

use DI\Container;
use DI\ContainerBuilder;

/**
 * Class Aesir
 * @package Aesir\v1
 */

final class Aesir
{
    /** @var Config */
    private $config;

    /** @var Router */
    private $router;


    /******* PUBLIC API ******
     *
     * @param Config $config
     * @param Router $router
     */
    public function __construct(Config $config, Router $router = null)
    {
        $this->config = $config;

        // Setup framework path
        $this->config->set('aesir/path', dirname(__DIR__));

        if(!is_null($router)) {
            $this->router = $router;

            // Set up Dependency Injection
            $builder = new ContainerBuilder(Container::class);

            $builder->useAutowiring($this->config->get('dependencies/autowiring', true));

            // Core dependency definitions
            $aesir = $this; // for use in config file
            $builder->addDefinitions(include($this->config->get('aesir/path').'/config/dependencies.php'));

            // Custom dependency definitions from the application
            $localDependencies = $this->config()->get('path') . $this->config()->get('dependencies', 'aesir.dependencies.php');

            if($localDependencies && file_exists($localDependencies)) {
                $builder->addDefinitions(include($localDependencies));
            }

            $this->router->setupContainer($builder->build());
        }
    }

    /**
     * Returns an instance of the Aesir Router.
     *
     * @return Router
     * @throws MissingDependency
     */
    public function router() {
        if(!is_null($this->router)) {
            return $this->router;
        } else {
            throw new MissingDependency("Router not set up for this instance");
        }
    }

    public function config() {
        return $this->config;
    }
}