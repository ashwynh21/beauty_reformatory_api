<?php

  // middleware
namespace br\Middleware;

// helpers
use br\Constants\Strings;
use br\Controllers\UserController;
use br\Helpers\Exception;
use br\Helpers\Request;
use br\Helpers\Response;
use br\Models\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

// models

// constants

// dependencies

class UserMiddleware extends Middleware
{
  /**
   * @param Request $request
   * @param Response $response
   * @param $next
   * @return Response
   */
  public function __invoke(Request $request, Response $response, $next) {
    /*
     * We're going to be using this method to call sub methods defined in this class for validation. Since there
     * are many ways i would like to take in validating one user we have to do it this way without over complicating
     * the structure of the application.
     *
     * This style or organization of middleware is quite unusual i know but it was the only way that i could think
     * of at the time that would allow me to use different middleware called methods to validate. I dont like the
     * idea of having too many middleware classes that do a bunch of different things so i preferred to set up in
     * this way.
     */
    /** @var String $name */
    $name = explode('/', $request->getUri()->getPath());
    
    try {
      if ($name[2] === 'create') {
        $request = $this->_validate($request);
        return $next($request, $response);
      } else if ($name[2] === 'authenticate') {
        $request = $this->_access($request);
        return $next($request, $response);
      } else if ($name[2] === 'refresh') {
        $request = $this->_refresh($request);
        return $next($request, $response);
      } else if ($name[2] === 'profile') {
        /*
         * We must first authenticate this user
         */
        $request = $this->_auth($request);
    
        if ($name[3] === 'get') {
          return $next($request, $response);
        } else if ($name[3] === 'update') {
          $request = $this->_loose($request);
          return $next($request, $response);
        }
      } else if ($name[2] === 'friends' || $name[2] === 'circles' || $name[2] === 'emotions') {
        /*
         * We must first authenticate this user
         */
        $request = $this->_auth($request);
    
        return $next($request, $response);
      }
      throw new Exception(Strings::$UNKNOWN_USER_REQUEST[0]);
    } catch(Exception $e) {
      return $this->invalid_response($request, $response, $e);
    }
    
    /*
     * Then finally we call the next part of the application here
     */
  }
  
  /**
   * @param Request $request
   * @return Request
   * @throws Exception
   */
  public function _validate(Request $request) {
    $user = json_decode(json_encode($request->getParsedBody()));
  
    if ($this->check_fields($user)) {
      $u = User::fromJSON($user);
      return $request->withAttribute('user', $u);
      /*
       * Down here I could handle this response in my own way but for now will ignore
       */
    }
    throw new Exception(Strings::$MISSING_FIELDS[0]);
  }
  /**
   * @param Request $request
   * @return Request
   * @throws Exception
   */
  public function _access(Request $request) {
    $user = json_decode(json_encode($request->getParsedBody()));
    
    if($this->credentials_check($user)) {
      if ($u = $this->check_user($user->email))
        return $request->withAttribute('user', $u);
    }
    throw new Exception(Strings::$MISSING_FIELDS[0]);
  }
  
  /**
   * @param Request $request
   * @return Request
   * @throws Exception
   */
  public function _refresh(Request $request)
  {
    $user = json_decode(json_encode($request->getParsedBody()));
    
    if ($this->token_check($user->email, $user->token)) {
      if ($u = $this->check_user($user->email))
        return $request->withAttribute('user', $u);
    }
    throw new Exception(Strings::$MISSING_FIELDS[0]);
  }
  
  /**
   * @param Request $request
   * @return Request
   * @throws Exception
   */
  public function _auth(Request $request)
  {
    $u = json_decode(json_encode($request->getParsedBody()));
    
    if ($this->token_check($u->email, $u->token)) {
      try {
        /** @var User $user */
        if ($user = $this->check_user($u->email)) {
          $user->setToken($u->token);
          
          if ($decode = UserController::decode_token($user)) {
            if (($decode->exp - time()) < 300) {
              $user->setToken(UserController::generate_jwt($user));
            }
          }
          return $request->withAttribute('user', $user);
        } else {
          throw new Exception(Strings::$USER_NOT_EXIST[0]);
        }
      } catch (ExpiredException | SignatureInvalidException $e) {
        throw new Exception(Strings::$INVALID_TOKEN[0]);
      }
    }
    throw new Exception(Strings::$MISSING_FIELDS[0]);
  }
  
  /**
   * @param Request $request
   * @return Request
   */
  public function _loose(Request $request)
  {
    $user = json_decode(json_encode($request->getParsedBody()));
    
    return $request->withAttribute('user', $user);
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
    if (!isset($u->fullname))
      throw new Exception(Strings::$NOT_FOUND_FULLNAME[0]);
    if (!isset($u->password))
      throw new Exception(Strings::$NOT_FOUND_PASSWORD[0]);
    if (!isset($u->location))
      throw new Exception(Strings::$NOT_FOUND_LOCATION[0]);
    if (!isset($u->mobile))
      throw new Exception(Strings::$NOT_FOUND_MOBILE[0]);
    
    return true;
  }
  /**
   * @param object $u
   * @return bool
   * @throws Exception
   */
  private function credentials_check(object $u)
  {
  
    if (!isset($u->email))
      throw new Exception(Strings::$INCORRECT_USERNAME[0]);
    if (!isset($u->password))
      throw new Exception(Strings::$INCORRECT_USERNAME[0]);
    
    return true;
  }
  
  /**
   * @param string $email
   * @param string $token
   * @return bool
   * @throws Exception
   */
  private function token_check($email, $token)
  {
    if (!isset($email))
      throw new Exception(Strings::$NOT_FOUND_EMAIL[0]);
    if (!isset($token))
      throw new Exception(Strings::$NOT_FOUND_TOKEN[0]);
    
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
  private function invalid_response(Request $request, Response $response, \Exception $e) {
    return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
  }
}
