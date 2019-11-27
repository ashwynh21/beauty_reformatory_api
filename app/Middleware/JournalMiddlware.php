<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;
  
  class JournalMiddlware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[3] === 'create')
          $request = $this->_validate($request);
        else
          $request = $this->_proceed($request);
      } catch (Exception $e) {
        return $this->invalid_response($request, $response, $e);
      }
      return $next($request, $response);
    }
    
    private function _validate(Request $request)
    {
      return $request;
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _proceed(Request $request)
    {
      /*
       * This function must validate the id of a journal,
       * the user has to provide the id so we can validate its existence here
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      if (isset($user)) {
        return $request->withAttribute('journal', $user->getJournal());
      }
      throw new Exception(Strings::$NOT_FOUND_JOURNAL[0]);
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
