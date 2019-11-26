<?php
  
  
  namespace br\Controllers;
  
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Emotion;
  use br\Models\User;
  use Doctrine\ORM\ORMException;
  
  class EmotionController extends Controller
  {
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response)
    {
      /*
       * This request will allow the user to store their mood into their data store
       */
      /** @var User $user */
      $user = $request->getAttribute('user');
      /** @var double $mood */
      $mood = $request->getAttribute('mood');
      
      try {
        if ($user && $mood) {
          $emotion = new Emotion();
          $emotion->setMood($mood);
          $emotion->setUser($user);
          
          $user->addEmotion($emotion);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$EMOTION_RECORDED[0], $emotion->toJSON(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (ORMException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function get(Request $request, Response $response)
    {
      
      /** @var User $user */
      $user = $request->getAttribute('user');
      
      try {
        if ($user) {
          
          return $response->withResponse(Strings::$EMOTION_RECORDED[0], $user->getEmotions()->map(function (Emotion $e) {
            return $e->toJSON();
          })->toArray(), true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  }
