<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 3/19/2016
 * Time: 8:06 PM
 */

namespace Aesir\v1\Traits;

trait Singleton
{
    /** @var static[] */
    protected static $instance = [];

    /**
     * Private constructor, get_instance()
     */
    private function __construct() {}

    /**
     * Returns the instance of the singleton
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if(empty(static::$instance[$class])) {
            static::$instance[$class] = new static();
            static::$instance[$class]->initialize();
        }

        return static::$instance[$class];
    }

    /**
     * Pseudo-constructor to be overwritten by implementing classes,
     * this will be ran once upon creation of the singleton's instance.
     */
    abstract protected function initialize();
}