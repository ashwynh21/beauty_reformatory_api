<?php
  
  namespace br\Controllers;
  
  // helpers
  use br\Constants\Integers;
  use br\Constants\Strings;
  use br\Helpers\Exception;
  use br\Helpers\Request;
  use br\Helpers\Response;
  use br\Models\Friendship;
  use br\Models\User;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\ORMException;
  use Doctrine\ORM\Query\Expr\Join;
  use DoctrineExtensions\Query\Mysql\Greatest;
  use DoctrineExtensions\Query\Mysql\Least;
  use finfo;
  use Firebase\JWT\ExpiredException;
  use Firebase\JWT\JWT;
  use Firebase\JWT\SignatureInvalidException;
  use PHPMailer\PHPMailer\Exception as PHPMailerException;
  use PHPMailer\PHPMailer\PHPMailer;

// dependencies

// models

// constants

  class UserController extends Controller
  {
    public function recover(Request $request, Response $response)
    {
      /** @var object $user */
      $u = json_decode(json_encode($request->getParsedBody()));
      try {
        if ($user = $this->check_user($u->email)) {
          /**
           * We then send an email to the user at this point
           */
          $mail = new PHPMailer(TRUE);
        
          $mail->SMTPDebug = 0;
          $mail->setFrom('recovery@ashio.me', 'Beauty Reformatory');
          $mail->addAddress($user->getEmail(), $user->getFullname());
          $mail->Subject = 'Password Recovery';
          $mail->isHTML(true);
          $mail->Body = '<p><b>Hi ' . $user->getFullname() . '</b>, your password has been recovered, here you go... ' . $user->getPassword() . '</p>';
        
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = TRUE;
          $mail->SMTPSecure = 'tls';
          $mail->Username = 'ashwynh21@gmail.com';
          $mail->Password = 'as28hw01yn95ho61rt00on';
          $mail->Port = 587;
        
          if ($mail->send()) {
            return $response->withResponse(Strings::$EMAIL_SENT[0], $u, true, 200);
          }
          throw new Exception(Strings::$EMAIL_NOT_SENT[0]);
        }
        throw new Exception(Strings::$USER_NOT_EXIST[0]);
      } catch (PHPMailerException | Exception $e) {
        return $response->withResponse($e->getMessage(), $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
    
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
        $user = $this->check_user($request->getAttribute('user')->email);
        
        if($user) {
          // check if user middleware worked then validate user's username and password
          if($this->validate_credentials($request->getAttribute('user'))) {
            // with the user validated we can now generate a token for them to gain access to the system
            // resources
            $user->setFirebase($request->getAttribute('user')->firebase);
            $this->manager->flush();
  
            $payload = array_merge((array)$this->clean_user($user->toJSON()), ['token' => $this->generate_jwt($user)], array('password' => $user->getPassword()));
            return $response->withResponse(Strings::$SIGNIN_SUCCESS[0], $payload, true, 200);
          }
        }
        throw new Exception(Strings::$INCORRECT_USERNAME[0]);
      } catch (Exception | ORMException $e) {
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
  
        if (!$this->check_user($user->getEmail())) {
          /*
           * we then query the database to record the user's creation
           */
          $user->setSecret($user->getId() . $user->getPassword() . User::$code);
          $user->setHandle('@' . explode('@', $user->getEmail())[0]);
          $user->setImage(base64_encode(file_get_contents(__DIR__ . '/../../resources/assets/files/default_avatar.png')));
          $user->setFirebase($user->getFirebase());
          
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
      /** @var User $user */
      $user = $request->getAttribute('user');
      try {
        if ($decode = UserController::decode_token($user)) {
          if (($decode->exp - time()) < 300) {
            $payload = array_merge($request->getParsedBody(), ['token' => UserController::generate_jwt($user)]);
            return $response->withResponse(Strings::$TOKEN_REFRESH[0], $payload, true, 200);
          } else {
            return $response->withResponse(Strings::$TOKEN_REFRESH[0], $request->getParsedBody(), true, 200);
          }
        }
        throw new Exception(Strings::$INVALID_TOKEN[0]);
      } catch (ExpiredException $e) {
        $payload = array_merge($request->getParsedBody(), ['token' => UserController::generate_jwt($user)]);
        return $response->withResponse(Strings::$TOKEN_REFRESH[0], $payload, true, 200);
      } catch (SignatureInvalidException $e) {
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
        $user = $this->check_user($request->getAttribute('user')->getEmail());
      
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
        if ($user->email) {
          (
          $u = $this->check_user($user->email));
        
          if ($this->update_user($u, $user))
            return $response->withResponse(Strings::$UPDATE_SUCCESS[0], $this->clean_user($u->toJSON()), true, 200);
        }
        throw new Exception(Strings::$UNKNOWN_USER[0]);
      } catch (Exception | ORMException $e) {
        return $response->withResponse(Strings::$UNKNOWN_USER[0], $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    public function find(Request $request, Response $response)
    {
    
      try {
      
        /** @var string $find */
        $find = $request->getParsedBody()['find'];
        /** @var User $user */
        $user = $request->getAttribute('user');
      
        if (isset($find)) {
          /*
           * The addition that needs to be made is an association with the user that is making the find
           * request, the results should basically depict the relation that is had with this user in terms
           * of their friendship.
           *
           * Note that we have added an outer join to the query set so that we are able to determine the
           * relation between the user and the person they are searching for should there be any
           */
          $this->manager->getConfiguration()->addCustomStringFunction('LEAST', Least::class);
          $this->manager->getConfiguration()->addCustomStringFunction('GREATEST', Greatest::class);
        
          $payload = $this->manager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.id', 'u.email', 'u.fullname', 'u.image', 'u.date', 'LEAST(COALESCE(f.state, 5), COALESCE(g.state, 5)) as state',)
            ->leftJoin(Friendship::class, 'f', Join::WITH,
              'u.id = f.subject')
            ->leftJoin(Friendship::class, 'g', Join::WITH,
              'u.id = f.initiator')
            ->where('u.fullname LIKE :find')
            ->orWhere('u.email LIKE :find')
            ->andWhere('u.id != :user')
            ->setParameter('find', "%$find%")
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getArrayResult();
        
          foreach ($payload as $person) {
            if (isset($person['image']) && strlen($person['image']) > 0)
              $person['image'] = $this->resize_image($person['image'], 0.08);
          }
        
          return $response->withResponse(Strings::$SEARCH_SUCCESS[0], $payload, true, 200);
        }
        throw new Exception(Strings::$SOMETHING_WRONG[0]);
      } catch (Exception $e) {
        return $response->withResponse(Strings::$SOMETHING_WRONG[0], $request->getParsedBody(), false, 401, $e->getTrace());
      }
    }
  
    private function resize_image($src, $scale)
    {
    
      if (!list($w, $h) = getimagesize('data://application/octet-stream;base64,' . $src)) return false;
    
      $type = strtolower(substr(strrchr($src, "."), 1));
      if ($type == 'jpeg') $type = 'jpg';
      switch ($type) {
        case 'bmp':
          $img = imagecreatefromwbmp($src);
          break;
        case 'gif':
          $img = imagecreatefromgif($src);
          break;
        case 'jpg':
          $img = imagecreatefromjpeg($src);
          break;
        case 'png':
          $img = imagecreatefrompng($src);
          break;
        default :
          return false;
      }
    
      // resize
      $width = $w * $scale;
      $height = $h * $scale;
    
      $ratio = min($width / $w, $height / $h);
      $width = $w * $ratio;
      $height = $h * $ratio;
      $x = 0;
    
      $new = imagecreatetruecolor($width, $height);
    
      // preserve transparency
      if ($type == "gif" or $type == "png") {
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
      }
    
      imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
    
      $dst = imagecreatetruecolor($w, $h);
      switch ($type) {
        case 'bmp':
          imagewbmp($new, $dst);
          break;
        case 'gif':
          imagegif($new, $dst);
          break;
        case 'jpg':
          imagejpeg($new, $dst);
          break;
        case 'png':
          imagepng($new, $dst);
          break;
      }
      ob_start();
      imagepng($dst);
      $data = ob_get_contents();
      ob_end_clean();
    
      return base64_encode($data);
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
     * @param User $old
     * @param object $new
     * @return boolean
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function update_user(User $old, object $new)
    {
      if (isset($new->fullname)) {
        $old->setFullname($new->fullname);
      }
      if (isset($new->image)) {
        /*
         * Here we must accommodate a blank string for if the user wants to remove their profile image
         */
        if ($new->image === '') {
          $old->setImage(base64_encode(file_get_contents(__DIR__ . '/../../resources/assets/files/default_avatar.png')));
        } else {
          $finfo = new finfo(FILEINFO_MIME);
          $mime = explode(';', $finfo->buffer(base64_decode($new->image)))[0];
          if ($this->validate_image($mime))
            $old->setImage($new->image);
        }
      }
      if (isset($new->location)) {
        $old->setLocation($new->location);
      }
      if (isset($new->mobile)) {
        $old->setMobile($new->mobile);
      }
      if (isset($new->handle)) {
        $old->setHandle($new->handle);
      }
      if (isset($new->status)) {
        $old->setStatus($new->status);
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
     * @param object $u
     * @return object|null
     */
    private function validate_credentials(object $u)
    {
      /** @var User $user */
      $user = $this->manager->getRepository(User::class)
        ->findOneBy(array(
          'email' => $u->email,
          'password' => $u->password,
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
