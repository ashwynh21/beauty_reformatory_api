<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;
  
  class NoteMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[5] === 'take' || $name[5] === 'update')
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
      /**
       * This function will be called when either creating or updating the user
       * information before sending the digested data to the controller for processing
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      $o = json_decode(json_encode($request->getParsedBody()));
      
      if ($o && $this->check_fields($o)) {
        // check if the task is existent
        if ($user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $o->task)))->first())
          return $request->withAttribute('note', $o);
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
      if (!isset($object->task))
        throw new Exception(Strings::$NOT_FOUND_TITLE[0]);
      if (!isset($object->note))
        throw new Exception(Strings::$NOT_FOUND_NOTE[0]);
      
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
