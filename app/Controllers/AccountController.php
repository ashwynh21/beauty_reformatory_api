<?php
  
  
  namespace br\Controllers;


  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Account;
  use br\Models\User;
  use Doctrine\ORM\ORMException;
  use Firebase\JWT\JWT;
  use Google_Service_Oauth2_Userinfoplus;

  class AccountController extends Controller
  {
    public function googleauth(Request $request, Response $response)
    {
      /**
       * At this point we expect to be recieving and google with obtained data so now we check the user
       */
      /** @var Google_Service_Oauth2_Userinfoplus $information */
      $information = $request->getAttribute('information');
    
      try {
        if (isset($information)) {
          /**
           * Now we check if the email is present in the external accounts entity
           *
           */
          $user = $this->check_user($information->getEmail());
        
          if (isset($user)) {
            $account = $this->manager->getRepository(Account::class)
              ->findOneBy(array(
                'user' => $user,
                'name' => 'google'
              ));
            if ($account) {
              $user->setFirebase($request->getParsedBody()['firebase']);
              $this->manager->flush();
            
              $payload = array_merge((array)$this->clean_user($user->toJSON()), ['token' => $this->generate_jwt($user)]);
              return $response->withResponse(Strings::$SIGNIN_SUCCESS[0], $payload, true, 200);
            }
            throw new Exception(Strings::$ACCOUNT_NOT_EXIST[0]);
          }
          throw new Exception(Strings::$USER_NOT_EXIST[0]);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function googlecreate(Request $request, Response $response)
    {
    
      /**
       * At this point we expect to be recieving and google with obtained data so now we check the user
       */
      /** @var Google_Service_Oauth2_Userinfoplus $information */
      $information = $request->getAttribute('information');
      try {
        if (isset($information)) {
          /**
           * Now we check if the email is present in the external accounts entity
           */
          if (!$this->check_user($information->getEmail())) {
            /** @var User $user */
            $user = new User();
            $user->setEmail($information->getEmail());
            $user->setFullname($information->getName());
            $user->setPassword(md5(random_bytes(64)));
            $user->setImage(base64_encode(file_get_contents($information->getPicture())));
            $user->setLocation($information->getLocale());
            $user->setFirebase($request->getParsedBody()['firebase']);
            /*
             * we then query the database to record the user's creation
             */
            $user->setSecret($user->getId() . $user->getPassword() . User::$code);
            $user->setHandle('@' . explode('@', $user->getEmail())[0]);
          
            /** @var Account $account */
            $account = new Account();
            $account->setUser($user);
            $account->setName('google');
          
            $user->addAccount($account);
          
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
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException | \Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function facebookauth(Request $request, Response $response)
    {
      /** @var object $information */
      $information = $request->getAttribute('information');
    
      try {
        if (isset($information)) {
          /**
           * Now we check if the email is present in the external accounts entity
           */
          $user = $this->check_user($information->email);
        
          if (isset($user)) {
            $account = $this->manager->getRepository(Account::class)
              ->findOneBy(array(
                'user' => $user,
                'name' => 'facebook'
              ));
            if ($account) {
              $user->setFirebase($request->getParsedBody()['firebase']);
              $this->manager->flush();
            
              $payload = array_merge((array)$this->clean_user($user->toJSON()), ['token' => $this->generate_jwt($user)]);
              return $response->withResponse(Strings::$SIGNIN_SUCCESS[0], $payload, true, 200);
            }
            throw new Exception(Strings::$ACCOUNT_NOT_EXIST[0]);
          }
          throw new Exception(Strings::$USER_NOT_EXIST[0]);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function facebookcreate(Request $request, Response $response)
    {
    
      /**
       * At this point we expect to be recieving and google with obtained data so now we check the user
       */
      /** @var object $information */
      $information = $request->getAttribute('information');
      try {
        if (isset($information)) {
          /**
           * Now we check if the email is present in the external accounts entity
           */
          if (!$this->check_user($information->email)) {
            /** @var User $user */
            $user = new User();
            $user->setEmail($information->email);
            $user->setFullname($information->name);
            $user->setPassword(md5(random_bytes(64)));
            $user->setImage(base64_encode(file_get_contents($information->picture->data->url)));
            $user->setFirebase($request->getParsedBody()['firebase']);
          
            if (isset($information->location))
              $user->setLocation($information->location);
            /*
             * we then query the database to record the user's creation
             */
            $user->setSecret($user->getId() . $user->getPassword() . User::$code);
            $user->setHandle('@' . explode('@', $user->getEmail())[0]);
          
            /** @var Account $account */
            $account = new Account();
            $account->setUser($user);
            $account->setName('facebook');
          
            $user->addAccount($account);
          
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
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception | ORMException | \Exception $e) {
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
            'disclaimer' => 'this token is property of beauty reformatory ' . date('Y') . '.',
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
