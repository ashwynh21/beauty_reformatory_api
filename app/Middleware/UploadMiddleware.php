<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Member;
  use Doctrine\Common\Collections\Criteria;
  
  class UploadMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[5] === 'upload')
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
      /**
       * With this function we will be making sure that the chat id that the user has sent belongs to the user
       * since the above layers have already validated that the user belongs in the circle then all we have
       * to do is make this check.
       */
      
      // first lets make sure the chats field is set since we already know that the other fields are set.
      $object = json_decode(json_encode($request->getParsedBody()));
      
      /** @var Member $member */
      $member = $request->getAttribute('membership');
      
      if ($this->check_fields($object)) {
        // Then we can check if the membership of the user has this chat message, and if it does
        // we can then forward the chat object to the controller for processing.
        
        if ($chat = $member->getChats()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $object->chat)))->first()) {
          $request = $request->withAttribute('upload', $object->upload);
          return $request->withAttribute('chat', $chat);
        }
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
      // first lets make sure the chats field is set since we already know that the other fields are set.
      $object = json_decode(json_encode($request->getParsedBody()));
      
      /** @var Member $member */
      $member = $request->getAttribute('membership');
      
      if ($this->check_fields($object)) {
        // Then we can check if the membership of the user has this chat message, and if it does
        // we can then forward the chat object to the controller for processing.
        
        if ($chat = $member->getChats()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $object->chat)))->first()) {
          $request = $request->withAttribute('upload', $object->upload);
          return $request->withAttribute('chat', $chat);
        }
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
      if (!isset($o->chat))
        throw new Exception(Strings::$NOT_FOUND_MESSAGE[0]);
      if (!isset($o->upload))
        throw new Exception(Strings::$NOT_FOUND_UPLOAD[0]);
      
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
