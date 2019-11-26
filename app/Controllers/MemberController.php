<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Circle;
  use br\Models\Friendship;
  use br\Models\Member;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  
  class MemberController extends Controller
  {
    public function addmember(Request $request, Response $response)
    {
      /*
       * This function will be responsible for adding a member of the users
       * friends to a named circle. The name is actually the id of the circle.
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var Circle $circle */
      $circle = $request->getAttribute('circle');
      /** @var User $friend */
      $friend = $request->getAttribute('member');
      
      try {
        /*
         * Now here we then get the users circle with the given id, then get the friendship with the
         * given friendship id then finally add the friendship to the members of the circle
         *
         * We also need to check if the user that is going to be added is not already part of the
         * circle.
         *
         * Considering all the checks that have been done in this controller function, we have no choice
         * but to perform the tasks in this fashion, because there is not enough middleware layered over
         * this controller.
         */
        
        // First check if the user exists...then check if they are friends
        if ($friendship = $this->check_friendship($user, $friend)) {
          if ($friendship->getState() != Integers::$ACCEPTED)
            throw new Exception(Strings::$MEMBER_NOT_FRIENDS[0]);
          // With the friendship checked and confirmed we can then check if the user is not already a member
          // of the circle
          /** @var Member $member */
          $member = $circle->getMembers()->matching(Criteria::create()->where(Criteria::expr()->eq('friendship', $friendship)))->first();
          // If the member exists then the user has already added the member before
          if ($member) {
            /*
             * check if the member is active or not
             */
            if ($member->getState() !== Integers::$MEMBER_ACTIVE) {
              // then we simply update the members state
              $member->setState(Integers::$MEMBER_ACTIVE);
              $this->manager->flush();
              
              return $response->withResponse(Strings::$MEMBER_ADD_SUCCESS[0], $member->toJSON(), true, 200);
            }
            throw new Exception(Strings::$MEMBER_ALREADY_IN_CIRCLE[0]);
          } else {
            // This is a new member and we can add the member to the circle.
            $member = new Member();
            $member->setFriendship($friendship);
            $member->setCircle($circle);
            $member->setState(Integers::$MEMBER_ACTIVE);
            
            $circle->addMember($member);
            
            // ORMException is thrown here
            $this->manager->flush();
            
            return $response->withResponse(Strings::$MEMBER_ADD_SUCCESS[0], $member->toJSON(), true, 200);
          }
        } else {
          throw new Exception(Strings::$FRIEND_NOT_FOUND[0]);
        }
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function removemember(Request $request, Response $response)
    {
      /*
       * This function will be used to remove a user from a circle, since we can safely assume
       * that this user is friends with the creator of the circle from the validation above through
       * the middleware
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var Circle $circle */
      $circle = $request->getAttribute('circle');
      /** @var User $friend */
      $friend = $request->getAttribute('member');
      
      try {
        /*
         * Just like when we are adding a user we must now check to make sure that the user
         * is making a valid request to the system.
         *
         * We once again check if the two users are friends, and if the user is member of
         * the named circle.
         */
        
        // First check if the user exists...then check if they are friends
        if ($friendship = $this->check_friendship($user, $friend)) {
          if ($friendship->getState() != Integers::$ACCEPTED)
            throw new Exception(Strings::$MEMBER_NOT_FRIENDS[0]);
          // With the friendship checked and confirmed we can then check if the user is not already a member
          // of the circle
          /** @var Member $member */
          $member = $circle->getMembers()->matching(Criteria::create()->where(Criteria::expr()->eq('friendship', $friendship)))->first();
          // If the member exists then the user has already added the member before
          if ($member) {
            if ($member->getState() != Integers::$MEMBER_ACTIVE)
              throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
            /*
             * With all the checks having been done we can now safely remove the user from the given circle
             *
             * note that here we are actually deleting information from the database, specifically in this
             * join table members. This means that we cannot join data or tie data to this table and
             * considering that we must have group chat functionality we will have to figure something out.
             */
            $member->setState(Integers::$MEMBER_REMOVED);
            
            $this->manager->flush();
            
            return $response->withResponse(Strings::$MEMBER_REMOVED[0], $request->getParsedBody(), true, 200);
          } else {
            /*
             * In this case the member does not exist in the circle
             */
            throw new Exception(Strings::$USER_NOT_IN_CIRCLE[0]);
          }
        } else {
          throw new Exception(Strings::$FRIEND_NOT_FOUND);
        }
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function transfer(Request $request, Response $response)
    {
      /*
       * This controller function will be used to transfer a circle from one user
       * to another. Particularly, the creator of the circle and a member of the circle.
       *
       * since the middleware is layered to validate all the required objects that we
       * need the environment is set.
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var Circle $circle */
      $circle = $request->getAttribute('circle');
      /** @var User $friend */
      $friend = $request->getAttribute('member');
      
      try {
        // check if circle belongs to the user
        if ($circle->getCreator() === $user) {
          // First check if the user exists...then check if they are friends
          if ($friendship = $this->check_friendship($user, $friend)) {
            if ($friendship->getState() != Integers::$ACCEPTED)
              throw new Exception(Strings::$MEMBER_NOT_FRIENDS[0]);
            // With the friendship checked and confirmed we can then check if the user is not already a member
            // of the circle
            /** @var Member $member */
            $member = $circle->getMembers()->matching(Criteria::create()->where(Criteria::expr()->eq('friendship', $friendship)))->first();
            // If the member exists then the user has already added the member before
            if ($member) {
              if ($member->getState() != Integers::$MEMBER_ACTIVE)
                throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
              /*
               * Done checking, now lets transfer ownership
               */
              $creator = $member->getFriendship()->getSubject();
              if ($creator === $user)
                $creator = $member->getFriendship()->getInitiator();
              
              $circle->setCreator($creator);
              $this->manager->flush();
              
              return $response->withResponse(Strings::$MEMBER_REMOVED[0], $request->getParsedBody(), true, 200);
            } else {
              /*
               * In this case the member does not exist in the circle
               */
              throw new Exception(Strings::$USER_NOT_IN_CIRCLE[0]);
            }
          } else {
            throw new Exception(Strings::$FRIEND_NOT_FOUND);
          }
        }
        throw new Exception(Strings::$CIRCLE_NOT_FOUND[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    private function check_friendship(User $u, User $f)
    {
      /** @var Friendship $init */
      $init = $u->getInitiated()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('subject', $f)))->first();
      
      /** @var Friendship $sub */
      $sub = $u->getSubjected()->matching(Criteria::create()->andWhere(Criteria::expr()->eq('initiator', $f)))->first();
      
      return ($init) ? $init : $sub;
    }
  }
