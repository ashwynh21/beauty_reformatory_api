<?php
  
  
  namespace br\Middleware;
  
  // helpers
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Circle;
  use br\Models\Member;
  use Doctrine\Common\Collections\Criteria;
  
  class ChatMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'chat' && $name[5] === 'send')
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
    private function _id(Request $request)
    {
      /*
       * Here we will be checking for message sending validation purposes
       */
      
      $object = json_decode(json_encode($request->getParsedBody()));
      
      // check that all fields of object are present
      if (isset($object->circle)) {
        // now we need to confirm the other information for truth, such as
        // friendship.
        // Since we already have the member as a user object as well as the circle
        // validated we just need to check if the user and member friendship is valid
        
        /** @var Member $member */
        $member = $request->getAttribute('member');
        /** @var Circle $circle */
        $circle = $request->getAttribute('circle');
        
        $member = $circle->getMembers()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $member->getId())))->first();
        return $request->withAttribute('membership', $member);
      }
      throw new Exception(Strings::$CIRCLE_NOT_FOUND[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      /*
       * Here we will be checking for message sending validation purposes
       */
      
      $object = json_decode(json_encode($request->getParsedBody()));
      
      // check that all fields of object are present
      if ($this->check_fields($object)) {
        // now we need to confirm the other information for truth, such as
        // friendship.
        // Since we already have the member as a user object as well as the circle
        // validated we just need to check if the user and member friendship is valid
        
        /** @var Member $member */
        $member = $request->getAttribute('member');
        /** @var Circle $circle */
        $circle = $request->getAttribute('circle');
        
        $member = $circle->getMembers()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $member->getId())))->first();
        
        if ($member) {
          $request = $request->withAttribute('chat', $object->chat);
          
          if (isset($object->chat))
            return $request->withAttribute('membership', $member);
        }
      }
      throw new Exception(Strings::$SOMETHING_WRONG[0]);
    }
    
    /**
     * @param object $object
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $object)
    {
      if (!isset($object->circle))
        throw new Exception(Strings::$CIRCLE_NOT_FOUND[0]);
      if (!isset($object->chat))
        throw new Exception(Strings::$NOT_FOUND_MESSAGE[0]);
      
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
