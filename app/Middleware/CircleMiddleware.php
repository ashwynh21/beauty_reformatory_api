<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Circle;
  
  class CircleMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      /*
       * This is where we will be switching which validation method we use based on the request we
       * are handling
       */
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[3] === 'create' || $name[3] === 'update')
          $request = $this->_validate($request);
        else if ($name[3] === 'get')
          return $next($request, $response);
        else
          $request = $this->_id($request);
        
        return $next($request, $response);
      } catch (Exception $e) {
        return $this->invalid_response($request, $response, $e);
      }
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _id(Request $request)
    {
      $object = json_decode(json_encode($request->getParsedBody()));
      
      if (isset($object->circle)) {
        /*
         * If the fields are all okay then we can create the circle entity
         *
         * Here we check if the circle exists and pass if through to the controller,
         * this middleware function will be used to process functionality for circles that
         * already exist.
         */
        /** @var Circle $circle */
        $circle = $this->manager->getRepository(Circle::class)
          ->findOneBy(array(
            'id' => $object->circle,
          ));
        
        if ($circle)
          return $request->withAttribute('circle', $circle);
        
        throw new Exception(Strings::$CIRCLE_NOT_EXIST[0]);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      /*
       * This function will be used to create a circle for the user, we will first check the validity of the users
       * data first before we pass through to the controller relating to this middleware function.
       *
       * This function can also be used to validate information for the update request hence it will be used as
       * such
       */
      $object = json_decode(json_encode($request->getParsedBody()));
      
      if ($this->check_fields($object)) {
        /*
         * If the fields are all okay then we can create the circle entity
         */
        return $request->withAttribute('circle', $object);
      }
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param object $circle
     * @return boolean
     * @throws Exception
     */
    private function check_fields(object $circle)
    {
      if (!isset($circle->name))
        throw new Exception(Strings::$NOT_FOUND_CIRCLE_NAME[0]);
      // We make the cover of the circle optional
      
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
