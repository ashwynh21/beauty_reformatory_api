<?php
  
  
  namespace br\Middleware;
  
  // constants
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;

  // helpers
  // model

  class FriendshipMiddleware extends Middleware
  {
    /**
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
  
        if (
        !($name[3] === 'messaging' ||
          $name[3] === 'get' ||
          $name[3] === 'getinitiated' ||
          $name[3] === 'getsubjected'
        )
        ) {
          $request = $this->_validate($request);
        }
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
    private function _validate(Request $request)
    {
      $user = $request->getAttribute('user');
      $friends = json_decode(json_encode($request->getParsedBody()));
      
      if ($this->check_fields($friends)) {
        /*
         * Now that we have validated the fields of this request we can then run a check to see if these
         * users are actual users of this application.
         */
        
        /** @var User $subject */
        $subject = $this->check_user($friends->subject);
        if (!isset($subject)) {
          throw new Exception(Strings::$SUBJECT_NOT_EXIST[0]);
        }
        
        // checking if the initiator and subject are not the same
        if (($user->getId() === $subject->getId())) {
          throw new Exception(Strings::$SELF_FRIEND_ERROR[0]);
        }
        
        return $request->withAttribute('friend', $subject);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
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
     * @param object $f
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $f)
    {
      if (!isset($f->subject))
        throw new Exception(Strings::$NOT_FOUND_FRIEND[0]);
      
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
