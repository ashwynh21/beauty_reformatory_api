<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Note;
  use br\Models\Task;
  use br\Models\User;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\ORMException;
  
  class NoteController extends Controller
  {
    public function take(Request $request, Response $response)
    {
      /**
       * This function will allow the user to add a note to their task
       */
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var object $data */
      $data = $request->getAttribute('note');
      
      try {
        if ($user && $data) {
          /** @var Task $task */
          $task = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->task)))->first();
          /** @var Note $note */
          $note = new Note();
          
          $note->setTask($task);
          $note->setNote($data->note);
          
          $task->addNote($note);
          
          $this->manager->flush();
          
          return $response->withResponse(Strings::$NOTE_TAKEN[0], $note->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (ORMException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function get(Request $request, Response $response)
    {
      /**
       * This function will simply return a list of the notes taken on a task
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var Task $task */
      $task = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $request->getParsedBody()['task'])))->first();
      
      try {
        if ($task) {
          return $response->withResponse(Strings::$NOTE_LIST[0], $task->getNotes()->map(function (Note $note) {
            return $note->toJSON();
          })->toArray(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    public function update(Request $request, Response $response)
    {
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var object $data */
      $data = $request->getAttribute('note');
      
      try {
        if ($user && $data) {
          /** @var Note $note */
          $note = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->task)))->first()
            ->getNotes()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->note)))->first();
          
          if (!$note)
            throw new Exception(Strings::$SOMETHING_WRONG[0]);
          
          if (!$data->update)
            throw new Exception(Strings::$SOMETHING_WRONG[0]);
          $note->setNote($data->update);
          
          $this->manager->flush();
          
          return $response->withResponse(Strings::$NOTE_UPDATED[0], $note->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (ORMException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
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
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var object $data */
      $data = $request->getAttribute('note');
      
      try {
        if ($user && $data) {
          /** @var Note $note */
          $note = $user->getJournal()->getTasks()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->task)))->first()
            ->getNotes()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $data->note)))->first();
          
          if (!$note)
            throw new Exception(Strings::$SOMETHING_WRONG[0]);
          // $note->setNote($data->update);
          
          $this->manager->flush();
          
          return $response->withResponse(Strings::$NOTE_UPDATED[0], $note->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (ORMException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
      
    }
  }
