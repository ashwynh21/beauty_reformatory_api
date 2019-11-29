<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Daily;
  use br\Models\User;
  use DateTime;
  use Doctrine\Common\Collections\Criteria;
  
  class DailyController extends Controller
  {
    public function add(Request $request, Response $response)
    {
      
      /*
       * This function will be responsible for allowing the user to enter goals
       * into their journal
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var object $data */
      $data = $request->getAttribute('daily');
      
      try {
        if ($data) {
          $daily = new Daily();
          
          $daily->setJournal($user->getJournal());
          $daily->setDescription($data->description);
          $daily->setTime(new DateTime($data->time));
          $daily->setDuration(new DateTime($data->duration));
          
          $user->getJournal()->addDaily($daily);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$DAILY_ADDDED[0], $daily->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (\Exception | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function update(Request $request, Response $response)
    {
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $data = $request->getAttribute('daily');
      
      try {
        if ($data->daily) {
          /** @var Daily $daily */
          $daily = $user->getJournal()->getDailies()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->daily)))->first();
          
          if ($daily) {
            if ($data->description)
              $daily->setDescription($data->description);
            
            if ($data->duration)
              $daily->setDuration(new DateTime($data->duration));
            
            if ($data->time)
              $daily->setTime(new DateTime($data->time));
            
            $this->manager->flush();
            
            return $response->withResponse(Strings::$DAILY_UPDATED[0], $daily->toJSON(), true, 200);
          }
          throw new Exception(Strings::$NOT_FOUND_DAILY[0]);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (\Exception | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function get(Request $request, Response $response)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      return $response->withResponse(Strings::$DAILY_LIST[0], $user->getJournal()->getDailies()->map(function (Daily $daily) {
        return $daily->toJSON();
      })->toArray(), true, 200);
    }
    
    public function remove(Request $request, Response $response)
    {
      /*
       * This function will be responsible for removing a note from a task
       *
       * Here we should introduce states to the note to create an abstraction
       * of removal but since the importance of the note is note great we can
       * then just delete the data from the system.
       */
    }
  }
