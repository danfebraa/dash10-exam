<?php

class Router
{
  private $request;
  private $supportedHttpMethods = array(
    "GET",
    "POST"
  );
  private $args;
  function __construct(IRequest $request)
  {
   $this->request = $request;
  }

  function __call($name, $args)
  {

    $this->args = $args;
    list($route, $method) = $args;
    

    if(!in_array(strtoupper($name), $this->supportedHttpMethods))
    {
      $this->invalidMethodHandler();
    }

    $this->{strtolower($name)}[$this->formatRoute($route)] = $method;
  }

  /**
   * Removes trailing forward slashes from the right of the route.
   * @param route (string)
   */
  private function formatRoute($route, $query_params = "")
  {
    $result = $query_params !== "" ? rtrim($route, "?". $query_params) : rtrim($route, '');
    
    if ($result === '')
    {
      return '/';
    }

    return $result;
  }

  private function invalidMethodHandler()
  {
    header("{$this->request->serverProtocol} 405 Method Not Allowed");
  }

  private function defaultRequestHandler()
  {
    header("{$this->request->serverProtocol} 404 Not Found");
  }

  /**
   * Resolves a route
   */
  function resolve()
  {

    list($route) = $this->args;

    $method = $this->{strtolower($this->request->requestMethod)}[$this->formatRoute($route)];
    
    if(is_null($method))
    {
      $this->defaultRequestHandler();
      return;
    }

    echo call_user_func_array ($method, array($this->request));
  }

  function __destruct()
  {
    $this->resolve();
  }
}