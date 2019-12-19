<?php
  
  namespace br\Controllers;
  // constants
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Friendship;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;

  // helpers
  // models
  // dependencies

  class FriendshipController extends Controller
  {
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addrequest(Request $request, Response $response)
    {
      /*
       * Here we will start by checking if the attribute is valid...
       */
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        /*
         * check to see if this relationship does not exist in any way
         */
        if ($this->check_friends($user, $friend)) {
          /** @var Friendship $friendship */
          $f = $user->getInitiated()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('subject', $friend)))->first();
          
          if ($f && $f->getState() == Integers::$DECLINED) {
            $f->setState(Integers::$PENDING);
            $this->manager->flush();
            return $response->withResponse(Strings::$FRIEND_REQUEST_SUCCESS[0], $this->clean_friends($f), true, 200);
          } else if ($f && $f->getState() == Integers::$REMOVED) {
            $f->setState(Integers::$PENDING);
            $this->manager->flush();
            return $response->withResponse(Strings::$FRIEND_REQUEST_SUCCESS[0], $this->clean_friends($f), true, 200);
          } else {
            $f = new Friendship();
            $f->setInitiator($user);
            $f->setSubject($friend);
            $f->setState(Integers::$PENDING);
            
            $user->addInitiated($f);
  
            /*
             * Considering that the user to recieve the request must be notified, we have to setup
             * a notification system that will allow the user to recieve push notifications. Considering
             * firebase as the notification system of choice.
             *
             * to get the request to work the user needs to be able to confirm the request as well as
             * be informed on who the user is that is making the request. This means that we might as
             * well send the user id to the firebase notification system then we can pack the data onto
             * the notification.
             */
            $i = $this->clean_user($user->toJSON());
            unset($i->image);
            unset($i->token);
            $s = $this->clean_user($friend->toJSON());
            unset($s->image);
            unset($s->token);
            $x = $this->clean_friends($f);
            unset($x->initiator->image);
            unset($x->initiator->token);
            unset($x->subject->image);
            unset($x->subject->token);
  
            $r = $this->firebase_request_notify((object)[
              'initiator' => $i,
              'subject' => $s,
              'friendship' => $x
            ]);
            $final = json_decode($r);
  
            if (isset($final->result) && $final->result === true) {
              $this->manager->flush();
              return $response->withResponse(Strings::$FRIEND_REQUEST_SUCCESS[0], $this->clean_friends($f), true, 200);
            }
  
            throw new Exception(Strings::$SOMETHING_WRONG[0]);
          }
        } else {
          throw new Exception(Strings::$FRIEND_NOT_FOUND[0]);
        }
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function approve(Request $request, Response $response)
    {
      /*
       * This method will allow users to approve of friend requests that they have received from other users
       */
      
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        /*
         * check to see if this relationship does not exist in any way. The reason that this section of code looks
         * weird is because of the type of check that has to be done before the request in flushed
         *
         * we have to check to make sure that the user is the subject of the friends object and that the user is not
         * an initiator of any kind between the two. Basically we're checking to see if the users are not already
         * friends, or if the user had not sent a request before, or if the user is trying to approve a request that
         * does not exist.
         */
        if ($this->check_approve($user, $friend)) {
          /** @var Friendship $f */
          $f = $user->getSubjected()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('initiator', $friend)))->first();
          $f->setState(Integers::$ACCEPTED);
          
          $this->manager->flush();
  
          return $response->withResponse(Strings::$APPROVAL_SUCCESS[0], $this->clean_friends($f), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function decline(Request $request, Response $response)
    {
      
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        /*
         * check to see if this relationship does not exist in any way. The reason that this section of code looks
         * weird is because of the type of check that has to be done before the request in flushed
         *
         * we have to check to make sure that the user is the subject of the friends object and that the user is not
         * an initiator of any kind between the two. Basically we're checking to see if the users are not already
         * friends, or if the user had not sent a request before, or if the user is trying to approve a request that
         * does not exist.
         */
        if ($this->check_approve($user, $friend)) {
          /** @var Friendship $f */
          $f = $user->getSubjected()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('initiator', $friend)))->first();
          $f->setState(Integers::$DECLINED);
          
          $this->manager->persist($f);
          $this->manager->flush();
  
          return $response->withResponse(Strings::$DECLINE_SUCCESS[0], $this->clean_friends($f), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function block(Request $request, Response $response)
    {
      /*
       * This controller functionality should work in general, this should simply override
       * any state that the friendship is in and put it in the blocked state.
       *
       * If the request does not exist then it should be created and then put into the blocked state.
       * This will allow users to block another user without having to send a request.
       */
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
  
  
        if ($f = $user->isFriend($friend)) {
          if ($f->getState() === Integers::$BLOCKED)
            throw new Exception(Strings::$REQUEST_BLOCKED[0]);
          
          $f->setState(Integers::$BLOCKED);
          $this->manager->flush();
          return $response->withResponse(Strings::$BLOCK_SUCCESS[0], $this->clean_friends($f), true, 200);
        } else {
          $f = new Friendship();
          $f->setInitiator($user);
          $f->setSubject($friend);
          $f->setState(Integers::$BLOCKED);
          
          $user->addInitiated($f);
          
          $this->manager->flush();
          return $response->withResponse(Strings::$BLOCK_SUCCESS[0], $this->clean_friends($f), true, 200);
        }
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function cancel(Request $request, Response $response)
    {
      /*
       * This function will allow a user who has made a request to another user to cancel their
       * request if the state of the request is still pending. In principle this will delete the request
       * from the friends table.
       *
       * Since we would prefer not to delete data off the database then we will create a state that
       * is no different to if the friend entity was not there, the $CANCELLED state.
       */
      
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        if ($this->check_friends($user, $friend)) {
          /** @var Friendship $friendship */
          $f = $user->getInitiated()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('subject', $friend)))->first();
          $f->setState(Integers::$CANCELLED);
          
          $this->manager->flush();
  
          return $response->withResponse(Strings::$CANCEL_SUCCESS[0], $this->clean_friends($f), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function remove(Request $request, Response $response)
    {
      /*
       * The request function will allow a user to terminate the relationship between themselves
       * and another user. technically this will deem the relationship as cancelled. Doing this
       * functionality this way prevents us from having to delete the users data of the relationship
       *
       */
      
      try {
        /** @var User $friend */
        $friend = $request->getAttribute('friend');
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        /** @var Friendship $friendship */
        if ($f = $user->isFriend($friend)) {
          $f->setState(Integers::$REMOVED);
          $this->manager->flush();
          return $response->withResponse(Strings::$USER_REMOVE_SUCCESS[0], $this->clean_friends($f), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function get(Request $request, Response $response)
    {
      /*
       * This request handling function will allow the user to get the list of their friends.
       * All we will do here is validate the user and select the user that share a relationship
       * with this user in the friends entity.
       */
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      try {
        if ($user) {
          /*
           * At this i've decided to try out a concept in Doctrine's ORM documentation that could prove to be very
           * useful. Im adding a friends property in the user model to make it easy to get a list of a user's friends.
           */
          return $response->withResponse(Strings::$FETCH_FRIENDS[0],
            array_merge(
              $user->getSubjected()->matching(Criteria::create()->where(Criteria::expr()->neq('state', Integers::$REMOVED)))->map(function (Friendship $friendship) {
                $friendship = $friendship->toJSON();
                $friendship->subject = $this->clean_user($friendship->subject);
                $friendship->initiator = $this->clean_user($friendship->initiator);
    
                return $friendship;
              })->toArray(),
              $user->getInitiated()->matching(Criteria::create()->where(Criteria::expr()->neq('state', Integers::$REMOVED)))->map(function (Friendship $friendship) {
                $friendship = $friendship->toJSON();
                $friendship->subject = $this->clean_user($friendship->subject);
                $friendship->initiator = $this->clean_user($friendship->initiator);
    
                return $friendship;
              })->toArray()
            ), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function getinitiated(Request $request, Response $response)
    {
    
      /*
       * This request handling function will allow the user to get the list of their friends.
       * All we will do here is validate the user and select the user that share a relationship
       * with this user in the friends entity.
       */
    
      /** @var User $user */
      $user = $request->getAttribute('user');
      try {
        if ($user) {
          /*
           * At this i've decided to try out a concept in Doctrine's ORM documentation that could prove to be very
           * useful. Im adding a friends property in the user model to make it easy to get a list of a user's friends.
           */
          return $response->withResponse(Strings::$FETCH_FRIENDS[0],
            $user->getInitiated()->matching(Criteria::create()->where(Criteria::expr()->eq('state', Integers::$ACCEPTED)))->map(function (Friendship $friendship) {
              $friendship = $friendship->toJSON();
              $friendship->subject = $this->clean_user($friendship->subject);
              $friendship->initiator = $this->clean_user($friendship->initiator);
            
              return $friendship;
            })->toArray(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function getsubjected(Request $request, Response $response)
    {
    
      /*
       * This request handling function will allow the user to get the list of their friends.
       * All we will do here is validate the user and select the user that share a relationship
       * with this user in the friends entity.
       */
    
      /** @var User $user */
      $user = $request->getAttribute('user');
      try {
        if ($user) {
          /*
           * At this i've decided to try out a concept in Doctrine's ORM documentation that could prove to be very
           * useful. Im adding a friends property in the user model to make it easy to get a list of a user's friends.
           */
          return $response->withResponse(Strings::$FETCH_FRIENDS[0],
          
            $user->getSubjected()->matching(Criteria::create()->where(Criteria::expr()->eq('state', Integers::$ACCEPTED)))->map(function (Friendship $friendship) {
              return $this->clean_user($friendship->getInitiator()->toJSON());
            })->toArray(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    /**
     * @param User $u
     * @param User $f
     * @return boolean
     * @throws Exception
     */
    private function check_approve(User $u, User $f)
    {
      /**
       * Getting request here, noting that I have to swap the initiator and subject because I'll get an unnecessary
       * not found error.
       * @var Friendship $friendship
       */
      $friendship = $u->getSubjected()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('initiator', $f)))->first();
      
      if ($friendship) {
        if ($friendship->getState() === Integers::$ACCEPTED)
          throw new Exception(Strings::$ALREADY_FRIENDS[0]);
        
        if ($friendship->getState() === Integers::$BLOCKED)
          throw new Exception(Strings::$REQUEST_BLOCKED[0]);
      } else {
        throw new Exception(Strings::$REQUEST_DOES_NOT_EXIST[0]);
      }
      
      return true;
    }
    
    /**
     * @param User $u
     * @param User $f
     *
     * @return boolean
     * @throws Exception
     */
    private function check_friends(User $u, User $f)
    {
      /** @var Friendship $friendship */
      $friendship = $u->getInitiated()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('subject', $f)))->first();
      
      if ($friendship) {
        if ($friendship->getState() === Integers::$PENDING)
          throw new Exception(Strings::$FRIEND_REQUEST_ALREADY_SENT[0]);
        
        if ($friendship->getState() === Integers::$PENDING && $friendship->getInitiator()->getId() == $friendship->getSubject()->getId())
          throw new Exception(Strings::$USER_ALREADY_SENT_REQUEST[0]);
        
        if ($friendship->getState() === Integers::$ACCEPTED)
          throw new Exception(Strings::$ALREADY_FRIENDS[0]);
        
        if ($friendship->getState() === Integers::$BLOCKED)
          throw new Exception(Strings::$REQUEST_BLOCKED[0]);
      }
      
      return true;
    }
    
    /**
     * @param Friendship $f
     * @return object
     */
    private function clean_friends(Friendship $f)
    {
      //unset($f->id);
      $initiator = $this->clean_user($f->getInitiator()->toJSON());
      $subject = $this->clean_user($f->getSubject()->toJSON());
  
      $result = $f->toJSON();
      $result->initiator = $initiator;
      $result->subject = $subject;
  
      return $result;
    }
    
    /**
     * @param object $u
     * @return object
     */
    private function clean_user(object $u)
    {
      unset($u->secret);
      unset($u->password);
      
      if (!$u->email)
        unset($u->email);
      if (!$u->fullname)
        unset($u->fullname);
      if (!$u->location)
        unset($u->location);
      if (!$u->mobile)
        unset($u->mobile);
      if (!$u->image)
        unset($u->image);
      if (!$u->handle)
        unset($u->handle);
      if (!$u->state)
        unset($u->state);
      if (!$u->token)
        unset($u->token);
      
      return $u;
    }
  
    private function firebase_request_notify(object $data)
    {
      $curl = curl_init(Strings::$FRIEND_REQUEST_URL[0]);
    
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
      $response = curl_exec($curl);
      curl_close($curl);
      return $response;
    }
  }
