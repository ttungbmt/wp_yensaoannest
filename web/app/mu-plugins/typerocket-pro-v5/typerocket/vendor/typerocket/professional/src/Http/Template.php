<?php
namespace TypeRocketPro\Http;

use TypeRocket\Http\Request;
use TypeRocket\Http\Responders\HttpResponder;
use TypeRocket\Http\Response;

class Template
{
    protected $methods = [];

    /**
     * @param string $method options: GET, POST, PUT, DELETE
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return Template
     */
    protected function method($method, $handler, $construct = [])
    {
        $this->methods[strtoupper($method)] = [$handler, $construct];

        return $this;
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function other($handler, $construct = [])
    {
        empty($this->methods['GET']) ? $this->get($handler, $construct) : null ;
        empty($this->methods['POST']) ? $this->post($handler, $construct) : null ;
        empty($this->methods['PUT']) ? $this->put($handler, $construct) : null ;
        empty($this->methods['DELETE']) ? $this->delete($handler, $construct) : null ;

        return $this;
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function any($handler, $construct = [])
    {
        $this->get($handler, $construct);
        $this->post($handler, $construct);
        $this->put($handler, $construct);
        $this->delete($handler, $construct);

        return $this;
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function get($handler, $construct = [])
    {
        return $this->method('GET', $handler, $construct);
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function post($handler, $construct = [])
    {
        return $this->method('POST', $handler, $construct);
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function put($handler, $construct = [])
    {
        return $this->method('PUT', $handler, $construct);
    }

    /**
     * @param callable|array|string $handler
     * @param array $construct
     *
     * @return $this
     */
    public function delete($handler, $construct = [])
    {
        return $this->method('DELETE', $handler, $construct);
    }

    /**
     * @param array $args
     * @param string|null $method options: GET, POST, PUT, DELETE
     */
    public function do($args = [], $method = null)
    {
        $method = $method ?? Request::new()->getFormMethod();

        if(!empty($this->methods[$method])) {
            [$handler, $construct] = $this->methods[$method];

            static::respond($handler, $args, $construct);
        }

        wp_die(__('Request method not supported for this template.', 'typerocket-domain'));
    }

    /**
     * Run responder as template
     *
     * @param callable|array|string $handler
     * @param array $args passed values to handler's method
     * @param array $construct passed values to handler's constructor
     */
    public static function respond($handler, $args = [], $construct = [])
    {
        $responder = new HttpResponder;

        $responder
            ->getHandler()
            ->setTemplate()
            ->setController($handler, $construct);

        $responder->respond( $args );
        Response::getFromContainer()->finish();
    }
}