<?php
  
  
  namespace br\Controllers;
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Journal;
  use br\Models\User;
  use Doctrine\ORM\ORMException;
  
  class JournalController extends Controller
  {
    public function create(Request $request, Response $response)
    {
      /*
       * This request will be used when setting up the users account when they have signed up
       * so we will call this request to complete the sign up and create a journal for the user
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      try {
        if ($user) {
          $journal = new Journal();
          $journal->setViewing(true);
          $journal->setUser($user);
          
          $user->setJournal($journal);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$JOURNAL_CREATED[0], $journal->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function toggle(Request $request, Response $response)
    {
      /*
       * Here we will simply allow a validated user to toggle the viewing state of their journal
       * from either true or false. The viewing state is what allows content creators of the
       * application to give reviews on a users journal over their 16 day reformatory period.
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      try {
        if ($user) {
          /** @var Journal $journal */
          $journal = $user->getJournal();
          
          $journal->setViewing(!$journal->isViewing());
          $this->manager->flush();
          return $response->withResponse(Strings::$JOURNAL_TOGGLED[0], $journal->toJSON(), true, 200);
        }
        
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
