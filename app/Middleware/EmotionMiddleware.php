<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  
  class EmotionMiddleware extends Middleware
  {
    
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[3] === 'create') {
          $request = $this->_validate($request);
        } else {
          $request = $this->_id($request);
        }
        
      } catch (Exception $e) {
        return $this->invalid_response($request, $response, $e);
      }
      return $next($request, $response);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      $object = json_decode(json_encode($request->getParsedBody()));
      
      if ($this->check_fields($object))
        return $request->withAttribute('mood', $object->mood);
      
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _id(Request $request)
    {
      $o = json_decode(json_encode($request->getParsedBody()));
      
      
      if (isset($o->email)) {
        return $request;
      }
      
      throw new Exception(Strings::$NOT_FOUND_USER[0]);
    }
    
    /**
     * @param object $o
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $o)
    {
      if (!isset($o->email))
        throw new Exception(Strings::$NOT_FOUND_USER[0]);
      if (!isset($o->mood))
        throw new Exception(Strings::$NOT_FOUND_MOOD[0]);
      
      return true;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param Exception $e
     * @return Response
     */
    private function invalid_response(Request $request, Response $response, Exception $e)
    {
      return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
    }
  }
