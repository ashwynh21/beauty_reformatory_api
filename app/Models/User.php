<?php
  
  namespace br\Models;
  
  // helpers
  // models
  
  // dependencies
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\Common\Collections\Criteria;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;

  /** @ORM\Entity @ORM\Table(name="users")*/
  class User
  {
    public static $code = 'as28hw01yn95ho61rt00on';
    
    /** @ORM\Id @ORM\Column(type="string")*/
    private $id;
    /** @ORM\Column(type="string")*/
    private $password;
    /** @ORM\Column(type="string") */
    private $handle;
    /** @ORM\Column(type="string")*/
    private $fullname;
    /**
     * @var string $status
     * @ORM\Column(type="text")
     */
    private $status;
    /** @ORM\Column(type="string") */
    private $state;
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
    /**
     * @var string $firebase
     * @ORM\Column(type="string")
     */
    private $firebase;
    /** @ORM\Column(type="datetime")*/
    private $date;
    
    private $token;
  
    /*
     * ORM variables go here
     */
  
    /**
     * @var ArrayCollection<Friendship> $initiated
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="initiator", cascade={"persist"})
     **/
    private $initiated;
    /**
     * @var ArrayCollection<Friendship> $subjected
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="subject", cascade={"persist"})
     **/
    private $subjected;
    /**
     * @var ArrayCollection<Circle> $circles
     * @ORM\OneToMany(targetEntity="Circle", mappedBy="creator", cascade={"persist"})
     */
    private $circles;
    /**
     * @var ArrayCollection<Account> $accounts
     * @ORM\OneToMany(targetEntity="Account", mappedBy="user", cascade={"persist"})
     */
    private $accounts;
    /**
     * @var ArrayCollection<Emotion> $emotions
     * @ORM\OneToMany(targetEntity="Emotion", mappedBy="user", cascade={"persist"})
     */
    private $emotions;
    /**
     * @var Journal
     * @ORM\OneToOne(targetEntity="Journal", mappedBy="user", cascade={"persist"})
     */
    private $journal;
    /**
     * @var ArrayCollection<Post> $posts
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user", cascade={"persist"})
     */
    private $posts;
    /**
     * @var ArrayCollection<Comment> $comments
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user", cascade={"persist"})
     */
    private $comments;
    /**
     * @var ArrayCollection<Abuse> $abuse
     * @ORM\OneToMany(targetEntity="Abuse", mappedBy="user", cascade={"persist"})
     */
    private $abuse;
    
    /**
     * User constructor.
     */
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->accounts = new ArrayCollection();
      $this->initiated = new ArrayCollection();
      $this->subjected = new ArrayCollection();
      $this->circles = new ArrayCollection();
      $this->emotions = new ArrayCollection();
      $this->abuse = new ArrayCollection();
      $this->date = new DateTime();
    }
    
    public function getId(){return $this->id;}
    public function getPassword(){return $this->password;}
  
    public function getState()
    {
      return $this->state;
    }
  
    public function getHandle()
    {
      return $this->handle;
    }
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
  
    public function setState($state)
    {
      $this->state = $state;
    }
  
    public function setHandle($handle)
    {
      $this->handle = $handle;
    }
  
    public function setImage($image){$this->image = $image;}
    public function setFullname($fullname){$this->fullname = $fullname;}
    public function setEmail($email){$this->email = $email;}
    public function setMobile($mobile){$this->mobile = $mobile;}
    public function setLocation($location){$this->location = $location;}
    public function setDate($date){$this->date = $date;}
    public function setSecret($secret){$this->secret = $secret;}
    public function setToken($token): void{$this->token = $token;}
  
    public function isFriend(User $friend)
    {
      $friendship = $this->initiated->matching(Criteria::create()->where(Criteria::expr()->eq('subject', $friend)))->first();
    
      if (!isset($friendship))
        $friendship = $this->subjected->matching(Criteria::create()->where(Criteria::expr()->eq('initiator', $friend)))->first();
    
      if ($friendship)
        return $friendship;
    
      return false;
    }
  
    /**
     * @return string
     */
    public function getFirebase(): string
    {
      return $this->firebase;
    }
  
    /**
     * @param string $firebase
     */
    public function setFirebase(string $firebase): void
    {
      $this->firebase = $firebase;
    }
    /**
     * @return ArrayCollection<Friendship>
     */
    public function getInitiated()
    {
      return $this->initiated;
    }
    /**
     * @param Friendship $initiated
     */
    public function addInitiated($initiated): void
    {
      $this->initiated->add($initiated);
    }
    /**
     * @return ArrayCollection<Friendship>
     */
    public function getSubjected()
    {
      return $this->subjected;
    }
    /**
     * @param Friendship $subjected
     */
    public function addSubjected($subjected): void
    {
      $this->subjected->add($subjected);
    }
    /**
     * @return Journal
     */
    public function getJournal(): Journal
    {
      return $this->journal;
    }
    /**
     * @param Journal $journal
     */
    public function setJournal(Journal $journal): void
    {
      $this->journal = $journal;
    }
    /**
     * @return ArrayCollection<Circle> | Collection<Circle>
     */
    public function getCircles(): Collection
    {
      return $this->circles;
    }
    /**
     * @param ArrayCollection<Circle> $circles
     */
    public function setCircles(ArrayCollection $circles): void
    {
      $this->circles = $circles;
    }
    /**
     * @param Circle $circle
     */
    public function addCircle(Circle $circle): void
    {
      $this->circles->add($circle);
    }
    /**
     * @return ArrayCollection
     */
    public function getAccounts(): ArrayCollection
    {
      return $this->accounts;
    }
    /**
     * @param ArrayCollection $accounts
     */
    public function setAccounts(ArrayCollection $accounts): void
    {
      $this->accounts = $accounts;
    }
  
    public function addAccount(Account $account)
    {
      $this->accounts->add($account);
    }
    /**
     * @return Collection<Emotion>
     */
    public function getEmotions(): Collection
    {
      return $this->emotions;
    }
    /**
     * @param ArrayCollection $emotions
     */
    public function setEmotions(ArrayCollection $emotions): void
    {
      $this->emotions = $emotions;
    }
    /**
     * @param Emotion $emotion
     */
    public function addEmotion(Emotion $emotion): void
    {
      $this->emotions->add($emotion);
    }
    /**
     * @return string
     */
    public function getStatus(): string
    {
      return $this->status;
    }
    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
      $this->status = $status;
    }
    /**
     * @return ArrayCollection | Collection
     */
    public function getComments(): Collection
    {
      return $this->comments;
    }
    /**
     * @param ArrayCollection $comments
     */
    public function setComments(ArrayCollection $comments): void
    {
      $this->comments = $comments;
    }
    /**
     * @return ArrayCollection | Collection
     */
    public function getPosts(): Collection
    {
      return $this->posts;
    }
    /**
     * @param ArrayCollection $posts
     */
    public function setPosts(ArrayCollection $posts): void
    {
      $this->posts = $posts;
    }
  
    /**
     * @return Collection
     */
    public function getAbuse(): Collection
    {
      return $this->abuse;
    }
  
    /**
     * @param ArrayCollection $abuse
     */
    public function setAbuse(ArrayCollection $abuse): void
    {
      $this->abuse = $abuse;
    }
  
    public function addAbuse(Abuse $abuse): void
    {
      $this->abuse->add($abuse);
    }
    /**
     * @return object
     */
    public function toJSON(){
      return (object)[
        'id' => $this->id,
        'password' => $this->password,
        'handle' => $this->handle,
        'status' => $this->status,
        'state' => $this->state,
        'image' => $this->image,
        'fullname' => $this->fullname,
        'email' => $this->email,
        'mobile' => $this->mobile,
        'secret' => $this->secret,
        'token' => $this->token,
        'location' => $this->location,
        'firebase' => $this->firebase,
        'date' => $this->date
      ];
    }
    /**
     * @param object $u
     * @return User
     */
    public static function fromJSON($u){
      $user = new User();
      if (isset($u->id)) $user->setId($u->id);
      if (isset($u->email)) $user->setEmail($u->email);
      if (isset($u->handle)) $user->setEmail($u->handle);
      if (isset($u->state)) $user->setState($u->state);
      if (isset($u->mobile)) $user->setMobile($u->mobile);
      if (isset($u->password)) $user->setPassword($u->password);
      if (isset($u->token)) $user->setToken($u->token);
      if (isset($u->fullname)) $user->setFullname($u->fullname);
      if (isset($u->image)) $user->setImage($u->image);
      if (isset($u->location)) $user->setLocation($u->location);
      if (isset($u->firebase)) $user->setFirebase($u->firebase);
      if (isset($u->date)) $user->setDate($u->date);
      if (isset($u->secret)) $user->setSecret($u->secret);
  
      return $user;
    }
  
  
  }
