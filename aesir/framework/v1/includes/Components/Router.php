<?php

namespace Aesir\v1\Components;

use DI\Container;
use Aesir\v1\Interfaces\IAesirRequest;
use Aesir\v1\Traits\Filterable;
use Aesir\v1\Traits\Singleton;
use Aesir\v1\Exceptions\AesirException;
use Aesir\v1\Exceptions\MissingDependency;
use DI\Definition\Helper\FactoryDefinitionHelper;

class Router
{
    use Filterable;

    /** @var array */
    protected $routes;

    /** @var string */
    protected $wpTemplate;

    /** @var Container */
    protected $container;

    /** @var IAesirRequest */
    protected $request;

    /** @var bool */
    protected $isListening;

    /** @var int */
    protected $priority;

    const ALL_ACCESS = 0x0000000;
    const ADMIN_ACCESS = 0x0000001;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->container = null;

        $this->isListening = false;

        $this->routes = [
            'GET'       => [],
            'POST'      => [],
            'PUT'       => [],
            'PATCH'     => [],
            'DELETE'    => [],
            '*'         => []
        ];
    }

    /**
     * Used to inject required dependencies in the router, the DependencyContainer is used to satisfy
     * dependencies for all constructors called by the router.
     *
     * @param Container $container
     */
    public function setupContainer(Container $container) {
        $this->container = $container;
    }

    /**
     * Adds a new dependency post-initialization.
     *
     * @param $class
     * @param $definition
     */
    public function addDependency($class, $definition) {
        $this->container->set($class, $definition);
    }

    /**
     * Causes the router to listen for incomming requests, the request object contains data needed
     * to properly route the request.
     *
     * @param IAesirRequest $request
     * @param int $priority
     *
     * @throws MissingDependency
     */
    public function listen(IAesirRequest $request, $priority = 10) {
        if(!is_null($this->container)) {
            if(!$this->isListening) {
                $this->isListening = true;
                $this->request = $request;
                $this->priority = $priority;

                $this->addFilter('template_include', 'routeRequest', $priority);
            }
        } else {
            throw new MissingDependency("DependencyContainer must be set before calls to Router::listen()");
        }
    }

    public function stop() {
        $this->isListening = false;
        static::removeFilters('template_include', 'routeRequest', $this->priority);
    }

    /**
     * Returns true if the Router is already listening for requests.
     * @return bool
     */
    public function isListening() {
        return $this->isListening;
    }

    /**
     * Adds a route that will respond to the specified type of HTTP requests.
     *
     * @param string $verb The HTTP verb to respond to, or "*" for any.
     * @param string $type The post type (or similar, such as author) that will
     *                      activate this route, or "*" for any type.
     * @param string $display_mode The display mode that this route will activate
     *                              for, such as "archive" or "single" (or "*").
     *                              For pages the template slug will select an
     *                              individual page template.
     * @param string $slug  A semi-unique identifier for the page/post you are viewing,
     *                      typically the url slug on singular display modes.
     * @param callable $controller The controller that will be activated when this
     *                              route is activated. This can be any callable,
     *                              see the PHP docs for examples. Note that unlike
     *                              normal callables class methods referenced using
     *                              static syntax do not need to be defined as static
     *                              methods. Instead, the constructor will be called
     *                              and the method will be ran under that instance.
     *
     */
    protected function add($verb, $type, $display_mode, $slug, $controller)
    {
        $verb = strtoupper($verb);

        if($type == null) {
            $type = '*';
        }

        if($display_mode == null) {
            $display_mode = '*';
        }

        if($slug == null) {
            $slug = '*';
        }

        if(empty($this->routes[$verb][$type])) {
            $this->routes[$verb][$type] = [];
        }

        if(empty($this->routes[$verb][$type][$display_mode])) {
            $this->routes[$verb][$type][$display_mode] = [];
        }

        $this->routes[$verb][$type][$display_mode][$slug] = $controller;
    }

    /**
     * Adds a route that will respond to any HTTP request.
     *
     * @see Router::add
     */
    public function any($type, $display_mode, $slug, $controller)
    {
        $this->add('*', $type, $display_mode, $slug, $controller);
    }

    /**
     * Adds a route that will respond to GET HTTP requests.
     *
     * @see Router::add
     */
    public function get($type, $display_mode, $slug, $controller)
    {
        $this->add('get', $type, $display_mode, $slug, $controller);
    }

    /**
     * Adds a route that will respond to POST HTTP requests.
     *
     * @see Router::add
     */
    public function post($type, $display_mode, $slug, $controller)
    {
        $this->add('post', $type, $display_mode, $slug, $controller);
    }

    /**
     * Adds a route that will respond to PATCH HTTP requests.
     *
     * @see Router::add
     */
    public function patch($type, $display_mode, $slug, $controller)
    {
        $this->add('patch', $type, $display_mode, $slug, $controller);
    }

    /**
     * Adds a route that will respond to PUT HTTP requests.
     *
     * @see Router::add
     */
    public function put($type, $display_mode, $slug, $controller)
    {
        $this->add('put', $type, $display_mode, $slug, $controller);
    }

    /**
     * Adds a route that will respond to DELETE HTTP requests.
     *
     * @see Router::add
     */
    public function delete($type, $display_mode, $slug, $controller)
    {
        $this->add('delete', $type, $display_mode, $slug, $controller);
    }

    public function getRequest() {
        return $this->request;
    }

    /**
     * Creates an Admin AJAX based route with dependency injection
     *
     * @param $verb
     * @param $action
     * @param $controller
     * @param int $priority
     * @param int $options
     */
    public function ajax($verb, $action, $controller, $priority = 10, $options = 0x0000000) {
        $adminOnly = $options & static::ADMIN_ACCESS;

        if(!$adminOnly) {
            add_action("wp_ajax_nopriv_{$action}", $this->resolveAjax($verb, $controller), $priority);
        }

        add_action("wp_ajax_{$action}", $this->resolveAjax($verb, $controller), $priority);
    }

    public function shortcode($slug, $controller) {
        add_action('wp', function() use ($slug, $controller) {
            add_shortcode($slug, function ($args, $content) use ($controller) {
                $view = $this->getView($controller, [$args, $content]);

                if(is_callable($view)) {
                    // Execute the view
                    ob_start();
                    $view();
                    return ob_get_clean();
                } else {
                    throw new AesirException("Returned view is not a callable object");
                }
            });
        });
    }

    /**
     * Generates a function that can be passed to the wp_ajax action to provide DI handling for AJAX calls.
     *
     * @param $controller
     *
     * @return \Closure
     */
    protected function resolveAjax($verb, $controller) {

        return function() use ($verb, $controller) {
            // Check to see if the request is valid for this method
            if($verb != '*' && strtoupper($verb) != $this->request->method()) return;

            // Give filters a chance to provide a controller
            $controller = apply_filters('aesir_ajax_routing_resolution', $controller, $this->request);

            if($controller) {
                $view = $this->getView($controller);

                if(is_callable($view)) {
                    // Execute the view
                    $view();
                    wp_die();
                } else {
                    throw new AesirException("Returned view is not a callable object");
                }

            }
        };
    }

    /**
     * Resolves the current route, loading the registered controller
     * if any, and passing it it's view and dependencies.
     *
     * @param string $template The wordpress template file being rendered
     *
     * @return bool
     * @throws AesirException
     */
    protected function resolve($template) {
        $this->wpTemplate = $template;
        $view = null;

        // Give filters a chance to provide a controller
        $controller = apply_filters('aesir_routing_resolution', false, $this->request);

        if(!$controller) {
            $controller = $this->getController(
                $this->request->method(),
                $this->request->type(),
                $this->request->mode(),
                $this->request->slug()
            );
        }

        if($controller) {
            $view = $this->getView($controller);

            if(is_callable($view)) {
                // Execute the view
                $view();
            } else {
                throw new AesirException("Returned view is not a callable object");
            }

        } else {
            return false;
        }

        return true;
    }

    protected function getView($controller, $parameters = []) {
        $object = $controller;

        $method = null;

        if(is_array($controller)) {
            $object = $controller[0];
            $method = $controller[1];
        }

        if(is_callable($object)) {
            $view = $this->container->call($object, $parameters);
        } elseif(is_callable([$object, $method])) {
            $view = $this->container->call([$object, $method], $parameters);
        } else {
            throw new AesirException("Controller method for route [{$this->request}] is not callable.");
        }

        return $view;
    }

    /**
     * Locates the first valid route and returns its controller.
     *
     * @param string $method
     * @param string $type
     * @param string $mode
     * @param string $slug
     *
     * @return bool|callable
     */
    protected function getController($method, $type, $mode, $slug) {
        $method = strtoupper($method);
        $type = (array) $type;

        foreach([$method, '*'] as $check_method) {
            if(!empty($this->routes[$check_method])) {
                foreach($type as $subtype) {
                    foreach ([$subtype, '*'] as $check_type) {
                        if (!empty($this->routes[$check_method][$check_type])) {
                            foreach ([$mode, '*'] as $check_mode) {
                                if (!empty($this->routes[$check_method][$check_type][$check_mode])) {
                                    foreach ([$slug, '*'] as $check_slug) {

                                        if (!empty($this->routes[$check_method][$check_type][$check_mode][$check_slug])) {
                                            return $this->routes[$check_method][$check_type][$check_mode][$check_slug];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Used to wrap the built in template loader, so that we can pass in
     * extra variables, load classes, etc.
     *
     * @param $template_path
     *
     * @return bool
     * @throws AesirException
     */
    protected function __routeRequest($template_path) {

        if(!$this->resolve($template_path)) {
            return $template_path;
        }

        return false;
    }
}