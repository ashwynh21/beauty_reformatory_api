<?php
  
  
  namespace br\Controllers;
  
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Friendship;
  use br\Models\Message;
  use Doctrine\ORM\ORMException;

  class MessageController extends Controller
  {
    public function send(Request $request, Response $response)
    {
      /*
       * For one user to send a message to another that, the two users must be friends
       * as depicted in the systems database relationships.
       */
      
      try {
        /** @var Message $message */
        $message = $request->getAttribute('message');
        /** @var Friendship $friendship */
        $friendship = $request->getAttribute('friendship');
        
        if ($message) {
          $message->setState(Integers::$SENT);
          
          $friendship->addMessage($message);
          
          /*
           * The error means that there is a related entity in a current object that has not been saved to the database
           * yet. You either need to make sure your relationship is set to cascade persist calls (meaning it will save
           * this automatically) or you need to persist the unsaved entity before saving the current entity.
           */
          $this->manager->flush();
          
          return $response->withResponse(Strings::$MESSAGE_SENT[0], $this->clean_message($message->toJSON()), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG);
      } catch (Exception | \Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    public function poll(Request $request, Response $response)
    {
      /*
       * As suggested by the name this function will allow the user to poll for
       * new messages from the application. This function will return a list of
       * those message nicely packed in a payload array.
       */
      /** @var Friendship $friendship */
      $friendship = $request->getAttribute('friendship');
      
      $messages = $this->get_clean_messages($friendship);
      
      return $response->withResponse(Strings::$MESSAGES_POLLED[0], $messages, true, 200);
    }
  
    public function paginate(Request $request, Response $response)
    {
      /*
       * This function will accept a page number and page size and fetch messages
       * belonging to the given user;
       */
    
      try {
        /** @var Friendship $friendship */
        $friendship = $request->getAttribute('friendship');
        /** @var object $details */
        $details = json_decode(json_encode($request->getParsedBody()));
      
        if (!isset($details->size))
          throw new Exception(Strings::$NOT_FOUND_SIZE[0]);
        if (!isset($details->page))
          throw new Exception(Strings::$NOT_FOUND_PAGE[0]);
      
        /** @var array $messages */
        $messages = $friendship->getMessages()
          ->map(function (Message $message) {
            return $this->clean_message($message->toJSON());
          })->slice($details->page * $details->size, $details->size);
      
        return $response->withResponse(Strings::$MESSAGES_POLLED[0], $messages, true, 200);
      } catch (Exception | \Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }

    /**
     * @param object $m
     * @return object
     */
    private function clean_message(object $m)
    {
      unset($m->sender);
      unset($m->recipient);
      
      return $m;
    }
    
    private function get_clean_messages(Friendship $friendship)
    {
      /** @var array $messages */
      $messages = $friendship->getMessages();
      
      $result = array();
      /** @var Message $message */
      foreach ($messages as $message) {
        array_push($result, $this->clean_message($message->toJSON()));
      }
      
      return $result;
    }
  }
