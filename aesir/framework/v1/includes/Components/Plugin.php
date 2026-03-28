<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 8/25/2017
 * Time: 4:44 PM
 */

namespace Aesir\v1\Components;

use Aesir\v1\Aesir;
use Aesir\v1\Exceptions\AesirException;
use Aesir\v1\Exceptions\MissingDependency;
use Aesir\v1\Interfaces\IAesirPlugin;
use Aesir\v1\Traits\Filterable;
use Aesir\v1\Traits\Singleton;


abstract class Plugin implements IAesirPlugin
{
    use Filterable;
    use Singleton;

    protected $path;
    protected $loaded = false;

    /** @var Aesir */
    private $aesir;

    protected function initialize()
    {

    }

    /**
     * This function serves as the entry point for the plugin, it should be run once in the main
     * plugin file. The path variable should always be the value of __FILE__. The data parameter
     * is for passing along any extra data that the plugin will need as an associative array, and
     * it will be made available for the plugin to use via the data($key) function.
     *
     * You should always call parent::load() when overriding this function.
     *
     * @param $path
     * @param Aesir|null $aesir
     * @param array $data
     *
     * @throws AesirException
     */
    public function load($path, Aesir $aesir) {

        if(!$this->loaded) {
            $this->loaded = true;
            $this->path = plugin_dir_path($path);
            $this->aesir = $aesir;

            $this->addAction('init', 'init');
            register_activation_hook($this->path, [$this, 'activate']);
            register_deactivation_hook($this->path, [get_class(), 'deactivate']);
            //register_uninstall_hook($this->path, [$this, 'uninstall']);
        } else {
            throw new AesirException("Cannot load plugin [{$this->path}], it is already loaded.");
        }

    }

    /**
     * Hooked to WP Init, this is where you should register post types and anything else that must happen each page load.
     */
    abstract protected function __init();

    /**
     * A hook that fires when the plugin is activated, use this to run any one-time setup code.
     */
    abstract protected function __activate();

    /**
     * A hook that fires when the plugin is deactivated, use this to run any cleanup code on deactivation.
     */
    static protected function __deactivate() {

    }

    /**
     * Retrieves the path to the plugin
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Returns this plugin's instance of the Aesir Router. If no router was set up for this plugin throws
     * a MissingDependency exception.
     *
     * @return Router
     * @throws MissingDependency
     */
    public function router() {
        return $this->aesir->router();
    }
}