<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;
  
  class AbuseMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        $request = $this->_verify($request);
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
    private function _verify(Request $request)
    {
      /*
       * This function will be called in the middleware to ensure that the user has
       * entered the proper information needed by the controller. Simple validation
       */
      $data = json_decode(json_encode($request->getParsedBody()));
      if ($this->check_fields($data) && $subject = $this->check_user($data->subject)) {
        return $request->withAttribute('subject', $subject);
      }
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param object $u
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $u)
    {
      if (!isset($u->subject))
        throw new Exception(Strings::$SUBJECT_NOT_EXIST[0]);
      if (!isset($u->description))
        throw new Exception(Strings::$NOT_FOUND_DESCRIPTION[0]);
      
      return true;
    }
    
    /**
     * @param string $u
     * @return User
     */
    private function check_user($u)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $u,
        ));
      return $user;
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @param \Exception $e
     * @return Response
     */
    private function invalid_response(Request $request, Response $response, \Exception $e)
    {
      return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
    }
  }
