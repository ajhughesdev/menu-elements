<?php
namespace Aesir\v1\Traits;


use Aesir\v1\Exceptions\AesirException;

trait Filterable
{
    protected static $hooks = [
        'filter' => [],
        'action' => []
    ];


    /**
     * Adds a filter to a method on this class, and tracks that filer internally with a static variable
     * so that any theme or plugin can reference the class statically to remove a filter set by it.
     *
     * @param string $tag
     * @param string $method
     * @param int $priority
     * @param int $args
     *
     * @throws AesirException
     */
    protected function addFilter($tag, $method, $priority = 10, $args = 1) {
        $this->addHook('filter', $tag, $method, $priority, $args);
    }

    /**
     * Adds an action to a method on this class, and tracks that action internally with a static variable
     * so that any theme or plugin can reference the class statically to remove an action set by it.
     *
     * @param string $tag
     * @param string $method
     * @param int $priority
     * @param int $args
     *
     * @throws AesirException
     */
    protected function addAction($tag, $method, $priority = 10, $args = 1) {
        $this->addHook('action', $tag, $method, $priority, $args);
    }

    /**
     * Adds a hook to a method on this class, and tracks that hook internally with a static variable
     * so that any theme or plugin can reference the class statically to remove a hook set by it.
     *
     * @param string $type
     * @param string $tag
     * @param string $method
     * @param int $priority
     * @param int $args
     *
     * @throws AesirException
     * @internal
     */
    protected function addHook($type, $tag, $method, $priority = 10, $args = 1) {

        if(!in_array($type, ['filter', 'action'])) {
            throw new AesirException('Hook type must be "filter" or "action"');
        }

        $add_hook = "add_{$type}";

        if(empty(static::$hooks[$type][$tag])) {
            static::$hooks[$type][$tag] = [];
        }

        if(empty(static::$hooks[$type][$tag][$method])) {
            static::$hooks[$type][$tag][$method] = [];
        }

        if(empty(static::$hooks[$type][$tag][$method][$priority])) {
            static::$hooks[$type][$tag][$method][$priority] = [];
        }

        static::$hooks[$type][$tag][$method][$priority][] = (object) ['class' => $this, 'enabled' => true];

        $me = $this;

        $add_hook($tag, function() use ($me, $type, $tag, $method, $priority) {
            foreach(static::$hooks[$type][$tag][$method][$priority] as $hook) {
                if($hook->enabled && $hook->class === $me) {
                    return call_user_func_array([$me, $method], func_get_args());
                } elseif($type == 'filter' && !$hook->enabled && $hook->class === $me) {
                    $args = func_get_args();
                    return $args[0];
                }
            }
        }, $priority, $args);
    }

    /**
     * Removes filers from methods in this class, you can omit the method name to remove filters
     * for that tag targeting any method of the class, or you can omit the priority to
     * remove all filers for that tag/method combo, regardless of priority.
     *
     * @param string $tag
     * @param string|null $method
     * @param int $priority
     */
    public static function disableFilters($tag, $method = null, $priority = -1) {
        static::changeHookStatus(false, 'filter', $tag, $method, $priority);
    }

    /**
     * Removes actions from methods in this class, you can omit the method name to remove filters
     * for that tag targeting any method of the class, or you can omit the priority to
     * remove all filers for that tag/method combo, regardless of priority.
     *
     * @param string $tag
     * @param string|null $method
     * @param int $priority
     */
    public static function disableActions($tag, $method = null, $priority = -1) {
        static::changeHookStatus(false, 'action', $tag, $method, $priority);
    }

    public static function enableActions($tag, $method = null, $priority = -1) {
        static::changeHookStatus(true, 'action', $tag, $method, $priority);
    }

    public static function enableFilters($tag, $method = null, $priority = -1) {
        static::changeHookStatus(true, 'filter', $tag, $method, $priority);
    }

    /**
     * Removes hooks from methods in this class, you can omit the method name to remove hooks
     * for that action targeting any method of the class, or you can omit the priority to
     * remove all hooks for that action/method combo, regardless of priority.
     *
     * @param string $type
     * @param string $tag
     * @param string|null $method
     * @param int $priority
     *
     * @internal
     */
    protected static function changeHookStatus($enabled, $type, $tag, $method = null, $priority = -1) {
        //$remove_hook = "remove_{$type}";

        if(!empty(static::$hooks[$type][$tag])) {
            if(empty($method)) {

                // Remove all filters for an action, regardless of method or priority
                foreach(static::$hooks[$type][$tag] as $_method => $priorities) {
                    foreach($priorities as $_priority => $filters) {
                        foreach($filters as $key => &$filter) {
                            //print_r($filter);
                            if(get_called_class() == get_class($filter->class)) {
                                //$remove_hook($tag, [$filter->class, $_method], $_priority);
                                $filter->enabled = $enabled;
                            }
                        }
                    }
                }

                // Clean up references
                //unset(static::$hooks[$type][$tag]);

            } elseif($priority === -1 && !empty(static::$hooks[$type][$tag][$method])) {

                // Remove all filters for an action/method combo, regardless of priority
                foreach(static::$hooks[$type][$tag][$method] as $_priority => $filters) {
                    foreach($filters as &$filter) {
                        if(get_called_class() == get_class($filter->class)) {
                            //$remove_hook($tag, [$filter->class, $method], $_priority);
                            $filter->enabled = $enabled;
                        }
                    }
                }

                // Clean up references
                //unset(static::$hooks[$type][$tag][$method]);

            } elseif(!empty(static::$hooks[$type][$tag][$method][$priority])) {

                // Remove all filters for an action/method/priority combo
                foreach(static::$hooks[$type][$tag][$method][$priority] as &$filter) {
                    if(get_called_class() == get_class($filter->class)) {
                        //$remove_hook($tag, [$filter->class, $method], $priority);
                        $filter->enabled = $enabled;
                    }
                }

                // Clean up references
                //unset(static::$hooks[$type][$tag][$method][$priority]);
            }
        }
    }

    /**
     * This call method allows you to map protected class methods to wordpress
     * filters and actions. This makes it possible (sort of) prevent unwanted
     * access to those functions and removes them from IDE autocomplete.
     *
     * Example:
     * add_filter('init', [$this, 'example_method']);
     *
     * Will call the method __example_method() in the current class.
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed|null
     */
    public function __call($name, array $arguments) {
        $methods = str_replace('__', '', get_class_methods($this));
        $callable = '__'.$name;

        if(in_array($name, $methods, true) && method_exists($this, $callable)) {
            return call_user_func_array([$this, $callable], $arguments);
        }

        return null;
    }
}