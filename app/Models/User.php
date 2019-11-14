<?php
  
  namespace br\Models;
  
  // helpers
    use br\Helpers\Exception;
  // models
  
  // dependencies
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  
  /** @ORM\Entity @ORM\Table(name="users")*/
  class User
  {
    public static $code = 'as28hw01yn95ho61rt00on';
    
    /** @ORM\Id @ORM\Column(type="string")*/
    private $id;
    /** @ORM\Column(type="string")*/
    private $password;
    /** @ORM\Column(type="string")*/
    private $fullname;
    /** @ORM\Column(type="string")*/
    private $image;
    /** @ORM\Column(type="string") */
    private $email;
    /** @ORM\Column(type="string") */
    private $mobile;
    /** @ORM\Column(type="string") */
    private $location;
    /** @ORM\Column(type="string") */
    private $secret;
    /** @ORM\Column(type="datetime")*/
    private $date;
    
    private $token;
    
    /**
     * User constructor.
     * @throws \Exception
     */
    public function __construct()
    {
      $this->date = new DateTime();
    }
    
    public function getId(){return $this->id;}
    public function getPassword(){return $this->password;}
    public function getImage(){return $this->image;}
    public function getFullname(){return $this->fullname;}
    public function getEmail(){return $this->email;}
    public function getMobile(){return $this->mobile;}
    public function getLocation(){return $this->location;}
    public function getDate(){return $this->date;}
    public function getSecret() {return $this->secret;}
    public function getToken(){return $this->token;}
    
    public function setId($id){$this->id = $id;}
    public function setPassword($password){$this->password = $password;}
    public function setImage($image){$this->image = $image;}
    public function setFullname($fullname){$this->fullname = $fullname;}
    public function setEmail($email){$this->email = $email;}
    public function setMobile($mobile){$this->mobile = $mobile;}
    public function setLocation($location){$this->location = $location;}
    public function setDate($date){$this->date = $date;}
    public function setSecret($secret){$this->secret = $secret;}
    public function setToken($token): void{$this->token = $token;}
    
    /**
     * @return array
     */
    public function toJSON(){
      return [
        'id' => $this->id,
        'password' => $this->password,
        'image' => $this->image,
        'fullname' => $this->fullname,
        'email' => $this->email,
        'mobile' => $this->mobile,
        'secret' => $this->secret,
        'token' => $this->token,
        'date' => $this->date
      ];
    }
  
    /**
     * @param $u
     * @return User
     * @throws Exception
     */
    public static function fromJSON($u){
      try {
        $user = new User();
        if(isset($u->id))$user->setId($u->id);
        if(isset($u->email))$user->setEmail($u->email);
        if(isset($u->mobile))$user->setMobile($u->mobile);
        if(isset($u->password))$user->setPassword($u->password);
        if(isset($u->token))$user->setToken($u->token);
        if(isset($u->fullname))$user->setFullname($u->fullname);
        if(isset($u->image))$user->setImage($u->image);
        if(isset($u->location))$user->setLocation($u->location);
        if(isset($u->date))$user->setDate($u->date);
        if(isset($u->secret))$user->setSecret($u->secret);
  
        return $user;
      } catch(\Exception $e) {
        throw new Exception($e->getMessage());
      }
    }
  }
