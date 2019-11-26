<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Chat;
  use br\Models\Member;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\ORM\ORMException;
  
  class ChatController extends Controller
  {
    public function send(Request $request, Response $response)
    {
      /*
       * This function will be used to send a message in a circle...
       *
       * From this we need to validate the member sending the message then
       * we need to validate the circle they are referencing, not in the same
       * order though.
       *
       * Here we expect that the member is available and that the circle is also
       * available with the user.
       */
      /** @var Member $membership */
      $membership = $request->getAttribute('membership');
      /** @var string $chat_message */
      $chat_message = $request->getAttribute('chat');
      
      try {
        // We first check to ensure that the user is a member of the circle
        if ($membership) {
          // now all we have to now is create the message chat object and add
          // it to the membership
          
          /** @var Chat $chat */
          $chat = new Chat();
          
          $chat->setMessage($chat_message);
          $chat->setState(Integers::$SENT);
          $chat->setMember($membership);
          
          $membership->addChats($chat);
          
          $this->manager->flush();
          return $response->withResponse(Strings::$MESSAGE_SENT[0], $chat->toJSON(), true, 200);
        }
        throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function poll(Request $request, Response $response)
    {
      /*
       * In this function we will simply retrieve messages within a given circle
       *
       * Noting that this is simple get request for data, which highlights the
       * importance of having a pagination and polling request within this
       * messaging set of functions for the circle as well as for the friendships.
       *
       * We must have this set of requests for the friendships messaging system as well.
       */
      /** @var Member $membership */
      $membership = $request->getAttribute('membership');
      /*
       * Having created this object we can use it to check if the user is a member of the name circle
       * in order to send them the messages of that circle
       */
      
      try {
        if ($membership) {
          // With membership confirmed we can now get the user the circles messages.
          $final = $membership->getCircle()->getMembers()->map(function (Member $member) {
            return $member->getChats()->map(function (Chat $chats) {
              return $chats->toJSON();
            })->toArray();
          })->toArray();
          
          return $response->withResponse(Strings::$MESSAGES_POLLED[0], $final, true, 200);
        }
        throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function paginate(Request $request, Response $response)
    {
      /*
       * This function will allow the user to poll a circles messages and retrieve
       */
      /*
       * In this function we will simply retrieve messages within a given circle
       *
       * Noting that this is simple get request for data, which highlights the
       * importance of having a pagination and polling request within this
       * messaging set of functions for the circle as well as for the friendships.
       *
       * We must have this set of requests for the friendships messaging system as well.
       */
      /** @var Member $membership */
      $membership = $request->getAttribute('membership');
      /*
       * Having created this object we can use it to check if the user is a member of the name circle
       * in order to send them the messages of that circle
       */
      
      try {
        if ($membership) {
          /** @var object $details */
          $details = json_decode(json_encode($request->getParsedBody()));
          
          if (!isset($details->size))
            throw new Exception(Strings::$NOT_FOUND_SIZE[0]);
          if (!isset($details->page))
            throw new Exception(Strings::$NOT_FOUND_PAGE[0]);
          
          // With membership confirmed we can now get the user the circles messages.
          /** @var ArrayCollection $chatting */
          $chatting = new ArrayCollection();
          $membership->getCircle()->getMembers()->map(function (Member $member) use ($chatting) {
            return $member->getChats()->map(function (Chat $chats) use ($chatting) {
              $chatting->add($chats);
              return $chats;
            });
          });
          /** @var array $final */
          $final = (array)$chatting->map(function (Chat $chats) {
            return $chats->toJSON();
          })->slice($details->page * $details->size, $details->size);
          
          return $response->withResponse(Strings::$MESSAGES_POLLED[0], $final, true, 200);
        }
        throw new Exception(Strings::$MEMBER_NOT_FOUND[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
