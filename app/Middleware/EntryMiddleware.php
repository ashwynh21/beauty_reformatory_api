<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  
  class EntryMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'add' || $name[4] === 'update')
          $request = $this->_validate($request);
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
      /*
       * Here we will be checking to make sure that the user has set the entry field
       * Then we can simply send to the controller
       */
      $o = json_decode(json_encode($request->getParsedBody()));
      
      if (!isset($o))
        throw new Exception(Strings::$MISSING_FIELDS[0]);
      
      if ($o->entry) {
        return $request->withAttribute('entry', $o->entry);
      }
      throw new Exception(Strings::$NOT_FOUND_ENTRY[0]);
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
