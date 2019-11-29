<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Goal;
  use br\Models\User;
  use DateTime;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  
  class GoalController extends Controller
  {
    public function add(Request $request, Response $response)
    {
      /*
       * This function will be responsible for allowing the user to enter goals
       * into their journal
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $data = $request->getAttribute('goal');
      
      try {
        if ($data) {
          $goal = new Goal();
          $goal->setJournal($user->getJournal());
          $goal->setDescription($data->description);
          $goal->setDue(new DateTime($data->due));
          $goal->setCompleted(false);
          
          $user->getJournal()->addGoal($goal);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$GOAL_ADDED[0], $goal->toJSON(), true, 200);
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
      
      $data = $request->getAttribute('goal');
      
      try {
        if ($data->goal) {
          /** @var Goal $goal */
          $goal = $user->getJournal()->getGoals()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->goal)))->first();
          
          $goal->setDescription($data->description);
          $goal->setDue(new DateTime($data->due));
          $user->getJournal()->addGoal($goal);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$GOAL_UPDATED[0], $goal->toJSON(), true, 200);
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
      
      return $response->withResponse(Strings::$GOAL_LIST[0], $user->getJournal()->getGoals()->map(function (Goal $goal) {
        return $goal->toJSON();
      })->toArray(), true, 200);
    }
    
    public function complete(Request $request, Response $response)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $id = $request->getParsedBody()['goal'];
      
      try {
        if ($id) {
          /** @var Goal $goal */
          $goal = $user->getJournal()->getGoals()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $id)))->first();
          
          $goal->setCompleted($goal->isCompleted());
          $goal->setFinish(new DateTime());
          $this->manager->flush();
          
          return $response->withResponse(Strings::$GOAL_COMPLETED[0], $goal->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
