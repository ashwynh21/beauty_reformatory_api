<?php
  
  
  namespace br\Middleware;

  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\User;
  use Google_Client;
  use Google_Service_Exception;
  use Google_Service_Oauth2;
  use InvalidArgumentException;

  class AccountMiddleware extends Middleware
  {
    public function __invoke(Request $request, Response $response, $next)
    {
      /** @var String $name */
      $name = explode('/', $request->getUri()->getPath());
    
      try {
        if ($name[3] === 'google_signin' || $name[3] === 'google_signup') {
          $request = $this->_validate($request);
        } else if ($name[3] === 'facebook_signin' || $name[3] === 'facebook_signup') {
          $request = $this->_verify($request);
        }
        return $next($request, $response);
      } catch (Exception | InvalidArgumentException | Google_Service_Exception $e) {
        return $this->invalid_response($request, $response, $e);
      }
    }
  
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _verify(Request $request)
    {
      /** @var object $a */
      $a = json_decode(json_encode($request->getParsedBody()));
    
      if (isset($a) && isset($a->token)) {
        $info = json_decode(file_get_contents('https://graph.facebook.com/v2.12/me?fields=name,location,email,picture&access_token=' . $a->token . ''));
      
        if (isset($info->email))
          return $request->withAttribute('information', $info);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
  
    /**
     * @param Request $request
     * @return Request
     * @throws Exception
     */
    private function _validate(Request $request)
    {
      /** @var object $a */
      $a = json_decode(json_encode($request->getParsedBody()));
    
      if (isset($a) && $this->check_fields($a)) {
        $client = $this->getclient($a->token);
        $service = new Google_Service_Oauth2($client);
      
        $info = $service->userinfo_v2_me->get();
        return $request->withAttribute('information', $info);
      }
      throw new Exception(Strings::$MISSING_FIELDS[0]);
    }
  
    /**
     * @param string $token
     * @return Google_Client
     * @throws Exception
     */
    private function getclient(string $token)
    {
      $client = new Google_Client();
      $client->setApplicationName('Beauty Reformatory');
    
      $client->setAccessType('offline');
      $client->setPrompt('select_account consent');
    
      $client->addScope('email');
      $client->addScope('profile');
    
      $client->setClientId(Strings::$GOOGLE_CLIENT[0]);
      $client->setClientSecret(Strings::$GOOGLE_SECRET[0]);
    
      // Load previously authorized token from a file, if it exists.
      // The file token.json stores the user's access and refresh tokens, and is
      // created automatically when the authorization flow completes for the first
      // time.
      $accessToken = $token;
      $client->setAccessToken($accessToken);
    
      return $client;
    }
  
    /**
     * @param object $u
     * @return bool
     * @throws Exception
     */
    private function check_fields(object $u)
    {
      if (!isset($u->email))
        throw new Exception(Strings::$NOT_FOUND_EMAIL[0]);
      if (!isset($u->token))
        throw new Exception(Strings::$NOT_FOUND_TOKEN[0]);
      if (!isset($u->firebase))
        throw new Exception(Strings::$FIREBASE_NOT_FOUND[0]);
    
      return true;
    }
  
    /**
     * @param string $u
     * @return User
     */
    private function check_user($u)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $u,
        ));
      return $user;
    }
  
    /**
     * @param Request $request
     * @param Response $response
     * @param \Exception $e
     * @return Response
     */
    private function invalid_response(Request $request, Response $response, \Exception $e)
    {
      return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
    }
  }
