<?php
  
  
  namespace br\Middleware;
  
  
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Friendship;
  use br\Models\Message;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;

  class MessageMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      
      try {
        
        /** @var String $name */
        $name = explode('/', $request->getUri()->getPath());
        
        if ($name[4] === 'send') {
          $request = $this->_validate($request);
        } else if ($name[4] === 'poll' || $name[4] === 'paged') {
          $request = $this->_retrieve($request);
        }
        /*
         * Message polling simply requests for a users messages, since at this level of the application we
         * already expect the user to have been authorized then can simply proceed to the controller
         */
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
    private function _retrieve(Request $request)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var object $friend */
      $f = json_decode(json_encode($request->getParsedBody()));
      
      if (!isset($f->friend))
        throw new Exception(Strings::$NOT_FOUND_RECIPIENT[0]);
      
      /** @var User $friend */
      if ($f && $friend = $this->check_user($f->friend)) {
        /** @var Friendship $friendship */
        if ($friend && $friendship = $this->check_friendship($user, $friend)) {
          return $request->withAttribute('friendship', $friendship);
        }
      }
      throw new Exception(Strings::$MESSAGE_TO_STRANGER[0]);
    }
    
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      $message = json_decode(json_encode($request->getParsedBody()));
      
      
      if ($this->check_fields($message)) {
        /*
         * With the fields check we must now make sure that the recipient of the user sending the message
         * knows the user, by checking if their relation show in the friends entity.
         */
        
        /** @var User $friend */
        if ($friend = $this->check_user($message->friend)) {
          if ($f = $this->check_friendship($user, $friend)) {
            /** @var Message $m */
            $m = new Message();
            
            $m->setSender($user);
            $m->setRecipient($friend);
            $m->setFriendship($f);
            $m->setMessage($message->message);
            
            $request = $request->withAttribute('friendship', $f);
            return $request->withAttribute('message', $m);
          } else
            throw new Exception(Strings::$MESSAGE_TO_STRANGER[0]);
        } else
          throw new Exception(Strings::$RECIPIENT_NOT_EXIST[0]);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
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
    private function check_user($email)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $email,
        ));
      return $user;
    }
    
    /**
     * @param User $sender
     * @param User $recipient
     * @return Friendship
     */
    private function check_friendship(User $sender, User $recipient)
    {
      /** @var Friendship $friendship */
      $f = $sender->getInitiated()->matching(
        Criteria::create()->andWhere(Criteria::expr()->eq('subject', $recipient)),
        )->matching(
        Criteria::create()->andWhere(Criteria::expr()->eq('state', Integers::$ACCEPTED)),
        )->first();
      
      /** @var Friendship $friendship */
      $g = $sender->getSubjected()->matching(
        Criteria::create()->andWhere(Criteria::expr()->eq('initiator', $recipient)),
        )->matching(
        Criteria::create()->andWhere(Criteria::expr()->eq('state', Integers::$ACCEPTED)),
        )->first();
      
      return ($f) ? $f : $g;
    }
    
    /**
     * @param object $message
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $message)
    {
      if (!isset($message->friend))
        throw new Exception(Strings::$NOT_FOUND_RECIPIENT[0]);
      if (!isset($message->message))
        throw new Exception(Strings::$NOT_FOUND_MESSAGE[0]);
      
      return true;
    }
  }
