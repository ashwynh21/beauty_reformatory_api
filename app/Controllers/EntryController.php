<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Entry;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  
  class EntryController extends Controller
  {
    public function add(Request $request, Response $response)
    {
      /*
       * Here we simply get the entry that has been passed by the middleware
       * and insert it into the users journal
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var string $entry */
      $e = $request->getAttribute('entry');
      
      try {
        
        /** @var Entry $entry */
        $entry = new Entry();
        $entry->setEntry($e);
        $entry->setJournal($user->getJournal());
        
        $user->getJournal()->addEntry($entry);
        
        $this->manager->flush();
        return $response->withResponse(Strings::$ENTRY_LOGGED[0], $entry->toJSON(), true, 200);
      } catch (ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function get(Request $request, Response $response)
    {
      /*
       * This function will give the user a list of their entries
       * this will be useful for when the user logs in to use in recovering their
       * data on to the application
       */
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $payload = $user->getJournal()->getEntries()->map(function (Entry $entry) {
        return $entry->toJSON();
      })->toArray();
      
      return $response->withResponse(Strings::$ENTRY_LIST[0], $payload, true, 200);
    }
    
    public function update(Request $request, Response $response)
    {
      /*
       * This controller function will allow the user to update any entry that they
       * had previously entered into their journal. So since we are not validating the
       * entry id in the middleware we have to do that here
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var string $entry */
      $e = $request->getAttribute('entry');
      /** @var string $update */
      $update = $request->getParsedBody()['update'];
      
      try {
        if ($user && $update) {
          /** @var Entry $entry */
          $entry = $user->getJournal()->getEntries()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $e)))->first();
          // Then we change the entry data.
          if ($entry) {
            
            $entry->setEntry($update);
            
            $this->manager->flush();
            
            return $response->withResponse(Strings::$ENTRY_UPDATED[0], $request->getParsedBody(), true, 200);
          }
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
