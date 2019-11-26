<?php
  
  namespace br\Controllers;
  
  // helpers
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\ORMException;
  use finfo;
  use Firebase\JWT\ExpiredException;
  use Firebase\JWT\JWT;
  use Firebase\JWT\SignatureInvalidException;

// dependencies

// models

// constants

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
        /** @var User $user */
        $user = $this->check_user($request->getAttribute('user')->getEmail());
        
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
  
        if (!$this->check_user($user)) {
          /*
           * we then query the database to record the user's creation
           */
          $user->setState($user->getId() . $user->getPassword() . User::$code);
          $user->setHandle('@' . explode('@', $user->getEmail())[0]);
          
          $this->manager->persist($user);
          $this->manager->flush();
    
          /*
           * Noticed that I had not been generating a JWT token for users that signed up so I just added the line
           * to do that. Not sure why I overlooked it but im leaving this comment just in case.
           */
          $user->setToken(UserController::generate_jwt($user));
    
          return $response->withResponse(Strings::$SIGNUP_SUCCESS[0], $this->clean_user($user->toJSON()), true, 200);
        } else {
          throw new Exception(Strings::$USER_EXISTS[0]);
        }
      } catch(Exception | \Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function refresh(Request $request, Response $response)
    {
      try {
      
        /** @var User $user */
        $user = $request->getAttribute('user');
      
        if ($decode = UserController::decode_token($user)) {
          if (($decode->exp - time()) < 300) {
            $payload = array_merge($request->getParsedBody(), ['token' => UserController::generate_jwt($user)]);
            return $response->withResponse(Strings::$TOKEN_REFRESH[0], $payload, true, 200);
          } else {
            return $response->withResponse(Strings::$TOKEN_REFRESH[0], $request->getParsedBody(), true, 200);
          }
        }
        throw new Exception(Strings::$INVALID_TOKEN[0]);
      } catch (ExpiredException | SignatureInvalidException $e) {
        return $response->withResponse(Strings::$INVALID_TOKEN[0], $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getprofile(Request $request, Response $response)
    {
      try {
      
        /** @var User $user */
        $user = $this->check_user($request->getAttribute('user'));
      
        if ($user) {
          /*
           * Since the user was queried from the database we can send the request with this object as the payload
           */
        
          /** @var array $payload */
          $payload = $this->clean_user($user->toJSON());
        
          return $response->withResponse(Strings::$PROFILE_SUCCESS[0], $payload, true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse(Strings::$SOMETHING_WRONG[0], $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function update(Request $request, Response $response)
    {
      try {
        /** @var object $user */
        $user = $request->getAttribute('user');
        if ($id = $user->getId()) {
          (
          $u = $this->check_user($user->getEmail()));
        
          if ($this->update_user($u, $user))
            return $response->withResponse(Strings::$UPDATE_SUCCESS[0], $this->clean_user($user->toJSON()), true, 200);
        }
        throw new Exception(Strings::$UNKNOWN_USER[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse(Strings::$UNKNOWN_USER[0], $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    /**
     * @param object $u
     * @return object
     */
    private function clean_user(object $u)
    {
      unset($u->id);
      unset($u->secret);
      unset($u->password);
  
      if (!isset($u->email))
        unset($u->email);
      if (!isset($u->fullname))
        unset($u->fullname);
      if (!isset($u->location))
        unset($u->location);
      if (!isset($u->mobile))
        unset($u->mobile);
      if (!isset($u->image))
        unset($u->image);
      if (!isset($u->handle))
        unset($u->handle);
      if (!isset($u->state))
        unset($u->state);
      if (!isset($u->mood))
        unset($u->mood);
    
      return $u;
    }
  
    /**
     * @param User $old
     * @param object $new
     * @return boolean
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function update_user(User $old, object $new)
    {
      if ($new->fullname) {
        $old->setFullname($new->fullname);
      }
      if ($new->image) {
        $finfo = new finfo(FILEINFO_MIME);
        $mime = explode(';', $finfo->buffer(base64_decode($new->image)))[0];
        if ($this->validate_image($mime))
          $old->setImage($new->image);
      }
      if ($new->location) {
        $old->setLocation($new->location);
      }
      if ($new->mobile) {
        $old->setMobile($new->mobile);
      }
    
      $this->manager->flush();
      return true;
    }
  
    /**
     * @param string $email
     * @return User
     */
    private function check_user(string $email)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $email,
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
    public static function generate_jwt(User $user)
    {
      $token = JWT::encode(
        array(
          'exp' => time() + Integers::$TOKEN_EXPIRATION,
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
  
    /**
     * @param User $user
     * @return object
     * @throws ExpiredException | SignatureInvalidException
     */
    public static function decode_token(User $user)
    {
      return JWT::decode($user->getToken(), $user->getSecret(), ['HS256']);
    }
  
    /**
     * @param string $mime
     * @return bool
     */
    private function validate_image(string $mime)
    {
      $types = array(
        'image/jpx',
        'image/jpm',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png'
      );
    
      return array_search($mime, $types) > 0;
    }
  }
