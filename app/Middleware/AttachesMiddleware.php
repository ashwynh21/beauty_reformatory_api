<?php
  
  
  namespace br\Middleware;
  
  // helpers
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Attaches;
  use br\Models\Message;
  use br\Models\User;
  
  class AttachesMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      try {
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'upload') {
          $request = $this->_validate($request);
        } else {
          $request = $this->_retrieve($request);
        }
        return $next($request, $response);
      } catch (Exception $e) {
        return $this->invalid_response($request, $response, $e);
      }
    }
    
    /*
     * In this middleware we will be validating file uploads that will be attached to messages belonging to a user.
     * We expect that the message id will be provided and that we should be able to derive the file type through
     * a specified field.
     *
     * We add the file type specification because we would like to limit the file that can be uploaded by the
     * application.
     */
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      $attachment = json_decode(json_encode($request->getParsedBody()));
      $user = $request->getAttribute('user');
      
      /*
       * Here we will be checking if the user id matches with the sender or recipient of the message id
       */
      if ($this->check_fields($attachment)) {
        $a = Attaches::fromJSON($attachment);
        $message = $this->manager->getRepository(Message::class)
          ->findOneBy(
            array('id' => $a->getMessage(),)
          );
        
        if ($message && $user->getId() === $message->getSender()) {
          /*
           * If this is true then it means the sender id of the message is the user id meaning that its
           * their message and its okay for them to attach their file.
           */
          
          $a = Attaches::fromJSON($a);
          
          if ($a)
            return $request->withAttribute('attaches', $a);
        } else
          throw new Exception(Strings::$INVALID_MESSAGE_ID[0]);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _retrieve(Request $request)
    {
      $attachment = json_decode(json_encode($request->getParsedBody()));
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      if ($this->check_fields($attachment)) {
        $a = Attaches::fromJSON($attachment);
        $a->setId($a->getAttachment());
        $message = $this->manager->getRepository(Message::class)
          ->findOneBy(
            array('id' => $a->getMessage(),)
          );
        
        if ($message && ($user->getId() === $message->getSender() || $user->getId() === $message->getRecipient())) {
          return $request->withAttribute('attaches', $a);
        } else
          throw new Exception(Strings::$INVALID_MESSAGE_ID[0]);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
    
    /**
     * @param object $a
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $a)
    {
      if (!isset($a->message))
        throw new Exception(Strings::$NOT_FOUND_MESSAGE[0]);
      if (!isset($a->attachment))
        throw new Exception(Strings::$NOT_FOUND_ATTACHMENT[0]);
      
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
