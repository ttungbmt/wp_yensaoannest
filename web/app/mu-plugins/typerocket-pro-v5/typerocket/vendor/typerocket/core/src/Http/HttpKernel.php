<?php
namespace TypeRocket\Http;

abstract class HttpKernel
{
    /** @var Request  */
    protected $request;
    /** @var Response  */
    protected $response;
    /** @var Handler */
    protected $handler;
    /** @var ControllerContainer */
    protected $controller;
    /** @var array  */
    protected $middleware = [];

    /**
     * Handle Middleware
     *
     * Run through global and resource level middleware.
     *
     * @param Request $request
     * @param Response $response
     * @param Handler $handler
     */
    public function __construct(Request $request, Response $response, Handler $handler) {
        $this->response = $response;
        $this->request = $request;
        $this->handler = $handler;
        do_action('typerocket_kernel', $this);
    }

    /**
     * Run Kernel
     * @throws \Exception
     */
    public function run()
    {
        $this->controller = new ControllerContainer($this->request, $this->response, $this->handler);
        $stack = new Stack( $this, $this->compileMiddleware() );
        $stack->handle($this->request, $this->response, $this->controller, $this->handler);
    }

    /**
     * Compile middleware from controller, router and kernel
     */
    public function compileMiddleware() : array {
        $stacks = [];

        // Route middleware
        $route = $this->handler->getRoute();
        if(!empty($route) && $route->middleware) {

            if(!is_array($route->middleware)) {
                $route->middleware = [$route->middleware];
            }

            $routeMiddleware = [];
            foreach ($route->middleware as $m) {
                $routeGroup = null;
                if(is_string($m)) {
                    $routeMiddleware = array_merge($routeMiddleware, $this->middleware[$m] ?? []);
                } else {
                    $routeMiddleware[] = $m;
                }
            }

            $stacks[] = $routeMiddleware;
        }

        // Handler middleware
        $groups = array_filter( $this->handler->getMiddlewareGroups() );
        foreach ($groups as $group) {
            if($group && !empty($this->middleware[$group])) {
                $stacks[] = $this->middleware[$group];
                break; // Take the first group only
            }
        }

        // Controller middleware
        $controllerMiddleware = [];
        $groups = $this->controller->getMiddlewareGroups();
        foreach( $groups as $group ) {
            $controllerMiddleware[] = $this->middleware[$group];
        }

        if( !empty($controllerMiddleware) ) {
            $stacks[] = call_user_func_array('array_merge', $controllerMiddleware);
        }

        // Global middleware
        $globalGroup = $this->handler->getHook() ? 'hooks' : 'http';
        $stacks[] = $this->middleware[$globalGroup];

        // Compile stacks
        $middleware = call_user_func_array('array_merge', $stacks);
        $middleware = array_reverse( array_unique($middleware) );

        return apply_filters('typerocket_middleware', $middleware, $globalGroup);
    }

}