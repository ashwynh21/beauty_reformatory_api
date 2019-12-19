<?php
  
  
  namespace br\Controllers;
  
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Abuse;
  use br\Models\User;
  use Doctrine\ORM\ORMException;
  
  class AbuseController extends Controller
  {
    public function report(Request $request, Response $response)
    {
      /*
       * Here we will the use the culprit user generated in the middleware to
       * add a report of abuse against them.
       */
      /** @var User $culprit */
      $culprit = $request->getAttribute('subject');
      
      try {
        
        if ($culprit) {
          $description = $request->getParsedBody()['description'];
          
          $abuse = new Abuse();
          $abuse->setDescription($description);
          $abuse->setUser($culprit);
          
          $culprit->addAbuse($abuse);
          $this->manager->flush();
          
          $payload = $abuse->toJSON();
          $payload->user = $this->clean_user($payload->user);
          
          return $response->withResponse(Strings::$ABUSE_REPORTED[0], $payload, true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
      
    }
    
    /**
     * @param object $u
     * @return object
     */
    private function clean_user(object $u)
    {
      unset($u->secret);
      unset($u->password);
      unset($u->image);
      unset($u->mood);
      unset($u->state);
      unset($u->mobile);
      unset($u->location);
      
      if (!isset($u->email))
        unset($u->email);
      if (!isset($u->fullname))
        unset($u->fullname);
      if (!isset($u->handle))
        unset($u->handle);
      
      return $u;
    }
  }
