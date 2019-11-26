<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Member;
  use br\Models\User;
  
  class MemberMiddleware extends Middleware
  {
    /*
     * This middleware will be responsible for validating the data input from users that have to do
     * with members of circles
     */
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'addmember' || $name[4] === 'removemember' || $name[4] === 'transfer')
          $request = $this->_validate($request);
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
    private function _validate(Request $request)
    {
      /*
       * Since at this level of the middleware we expect that the circle data
       * has been checked to see if the users circle exists. Now we can check if
       * the membership exists within the circle.
       */
      $object = json_decode(json_encode($request->getParsedBody()));
      
      if ($this->check_fields($object)) {
        /*
         * Fields have been checked for null now we can move forward, from here i think
         * we can just move into the controller to handle things there
         *
         * Considering that in whatever operation we have in the controller we will
         * need to check if the member specified in the request exists.
         */
        if ($member = $this->check_user($object->member))
          return $request->withAttribute('member', $member);
        
        throw new Exception(Strings::$USER_NOT_EXIST[0]);
      }
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _id(Request $request)
    {
      $object = json_decode(json_encode($request->getParsedBody()));
      
      if ($this->check_fields($object)) {
        if ($member = $this->manager->getRepository(Member::class)->findOneBy(array('id' => $object->member))) {
          return $request->withAttribute('member', $member);
        }
        
        throw new Exception(Strings::$USER_NOT_EXIST[0]);
      }
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param object $o
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $o)
    {
      if (!isset($o->member))
        throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
      
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
    
    /**
     * @param string $email
     * @return User
     */
    private function check_user(string $email)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $email,
        ));
      return $user;
    }
  }
