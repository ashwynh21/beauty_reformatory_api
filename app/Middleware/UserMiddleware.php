<?php

  // middleware
namespace br\Middleware;

// helpers
use br\Helpers\Exception;
use br\Helpers\Request;
use br\Helpers\Response;

// models
use br\Models\User;

// constants
use br\Constants\Strings;

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
     */
    /** @var String $name */
    $name = $request->getUri()->getPath();
    
    try {
      if ($name === '/users/create') {
        $request = $this->_validate($request);
        return $next($request, $response);
      } else if ($name === '/users/authenticate') {
        $request = $this->_access($request);
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
    $user = User::fromJSON(json_decode(json_encode($request->getParsedBody())));
  
    if ($this->check_fields($user)) {
      return $request->withAttribute('user', $user);
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
    $user = User::fromJSON(json_decode(json_encode($request->getParsedBody())));
    
    if($this->credentials_check($user)) {
      return $request->withAttribute('user', $user);
    }
    throw new Exception(Strings::$MISSING_FIELDS[0]);
  }
  
  /**
   * @param User $u
   * @return bool
   * @throws Exception
   */
  private function check_fields(User $u) {
  
    if(!($u->getEmail()))
      throw new Exception(Strings::$NOT_FOUND_EMAIL[0]);
    if(!($u->getFullname()))
      throw new Exception(Strings::$NOT_FOUND_FULLNAME[0]);
    if(!($u->getPassword()))
      throw new Exception(Strings::$NOT_FOUND_PASSWORD[0]);
    if(!($u->getLocation()))
      throw new Exception(Strings::$NOT_FOUND_LOCATION[0]);
    if(!($u->getMobile()))
      throw new Exception(Strings::$NOT_FOUND_MOBILE[0]);
    
    return true;
  }
  
  /**
   * @param User $u
   * @return bool
   * @throws Exception
   */
  private function credentials_check(User $u) {
  
    if(!($u->getEmail()))
      throw new Exception(Strings::$INCORRECT_USERNAME[0]);
    if(!($u->getPassword()))
      throw new Exception(Strings::$INCORRECT_USERNAME[0]);
    
    return true;
  }
  /**
   * @param Request $request
   * @param Response $response
   * @param \Exception $e
   * @return Response
   */
  private function invalid_response(Request $request, Response $response, \Exception $e) {
    return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401);
  }
}
