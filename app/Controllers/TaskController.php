<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Task;
  use br\Models\User;
  use DateTime;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  
  class TaskController extends Controller
  {
    
    public function add(Request $request, Response $response)
    {
      /*
       * This function will be responsible for allowing the user to enter goals
       * into their journal
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $data = $request->getAttribute('task');
      
      try {
        if ($data) {
          $task = new Task();
          
          $task->setTitle($data->title);
          $task->setJournal($user->getJournal());
          $task->setDescription($data->description);
          $task->setDue(new DateTime($data->due));
          $task->setCompleted(false);
          
          $user->getJournal()->addTask($task);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$TASK_ADDED[0], $task->toJSON(), true, 200);
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
      
      $data = $request->getAttribute('task');
      
      try {
        if ($data->task) {
          /** @var Task $task */
          $task = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->task)))->first();
          
          if ($data->title)
            $task->setTitle($data->title);
          if ($data->description)
            $task->setDescription($data->description);
          if ($data->due)
            $task->setDue(new DateTime($data->due));
          
          $this->manager->flush();
          
          return $response->withResponse(Strings::$TASK_UPDATED[0], $task->toJSON(), true, 200);
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
      
      return $response->withResponse(Strings::$TASK_LIST[0], $user->getJournal()->getTasks()->map(function (Task $task) {
        return $task->toJSON();
      })->toArray(), true, 200);
    }
    
    public function complete(Request $request, Response $response)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      $id = $request->getParsedBody()['task'];
      
      try {
        if ($id) {
          /** @var Task $task */
          $task = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $id)))->first();
          
          if ($task->isCompleted())
            throw new Exception(Strings::$TASK_ALREADY_COMPLETED[0]);
          
          $task->setCompleted(!$task->isCompleted());
          $task->setFinish(new DateTime());
          $this->manager->flush();
          
          return $response->withResponse(Strings::$TASK_COMPLETED[0], $task->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
