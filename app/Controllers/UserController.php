<?php
  
  namespace br\Controllers;
  
  // helpers
  use br\Helpers\Exception;
  use br\Helpers\Response;
  use br\Helpers\Request;
// dependencies
  use Doctrine\ORM\ORMException;
  use Firebase\JWT\JWT;
// models
  use br\Models\User;
// constants
  use br\Constants\Strings;
  
  class UserController extends Controller
  {
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function authenticate(Request $request, Response $response) {
      /*
       * This method will simply perform an authentication access check on the user being supplied. Upon success
       * the request will receive a response containing a JWT token that has an expiration.
       */
      try {
        $user = $request->getAttribute('user');
        
        if($user) {
          // check if user middleware worked then validate user's username and password
          if($this->validate_credentials($request->getAttribute('user'))) {
            // with the user validated we can now generate a token for them to gain access to the system
            // resources
            $payload = array_merge($request->getParsedBody(), ['token' => $this->generate_jwt($user)]);
            return $response->withResponse(Strings::$SIGNIN_SUCCESS[0], $payload, true, 200);
          }
        }
        throw new Exception(Strings::$INCORRECT_USERNAME[0]);
      } catch(Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response) {
      /*
       * This function will serve to create new users after being validated in middleware to establish
       * a data object
       */
      try{
        /** @var User $user */
        $user = $request->getAttribute('user');
        
        if(!$this->check_duplicates($user)) {
          /*
           * we then query the database to record the user's creation
           */
          $user->setId(md5(random_bytes(64)));
          $user->setSecret($user->getId() . $user->getPassword() . User::$code);
          
          $this->manager->persist($user);
          $this->manager->flush();
          
          return $response->withResponse(Strings::$SIGNUP_SUCCESS[0], $user->toJSON(), true, 200);
        } else {
          throw new Exception(Strings::$USER_EXISTS[0]);
        }
      } catch(Exception | \Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param User $u
     * @return User
     */
    private function check_duplicates(User $u) {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $u->getEmail(),
        ));
      return $user;
    }
    /**
     * @param User $u
     * @return object|null
     */
    private function validate_credentials(User $u) {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $u->getEmail(),
          'password' => $u->getPassword(),
        ));
      return $user;
    }
    /**
     * @param User $user
     * @return string
     */
    private function generate_jwt(User $user) {
      $token = JWT::encode(
        array(
          'exp' => time() + 1800,
          'iss' => 'https://www.beautyreformatory.com',
          'iat' => time(),
          
          'claims' => array(
            'recipient' => $user->getEmail(),
            'disclaimer' => 'this token is property of beauty reformatory '.date('Y').'.',
            'duration' => '30 minutes',
            'permission' => array('user')
          )
        ),
        $user->getSecret(),
        'HS256'
      );
      
      return $token;
    }
  }
