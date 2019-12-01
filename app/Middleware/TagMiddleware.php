<?php
  
  
  namespace br\Middleware;
  
  
  use br\Helpers\Request;
  use br\Helpers\Response;
  
  class TagMiddleware
  {
    
    public function __invoke(Request $request, Response $response, $next)
    {
      return $next($request, $response);
    }
  }
