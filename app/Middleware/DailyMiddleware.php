<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  
  class DailyMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'add' || $name[4] === 'update')
          $request = $this->_validate($request);
        else
          return $next($request, $response);
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
       * In this function we will be making sure that all the needed fields are set
       */
      $o = json_decode(json_encode($request->getParsedBody()));
      
      if (isset($o) && $this->check_fields($o)) {
        // At this point the fields have been check and we can proceed to sending the object to the controller
        // Since at this point we already have the user object we can check to see if the journal
        // id provided is valid
        return $request->withAttribute('daily', $o);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
    
    /**
     * @param object $object
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $object)
    {
      if (!isset($object->description))
        throw new Exception(Strings::$NOT_FOUND_DESCRIPTION[0]);
      if (!isset($object->time))
        throw new Exception(Strings::$NOT_FOUND_TIME[0]);
      if (!isset($object->duration))
        throw new Exception(Strings::$NOT_FOUND_DURATION[0]);
      
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
