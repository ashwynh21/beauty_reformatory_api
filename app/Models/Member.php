<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /** @ORM\Entity @ORM\Table(name="members") */
  class Member
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Circle $circle
     * @ORM\ManyToOne(targetEntity="Circle", inversedBy="members", cascade={"persist"})
     * @ORM\JoinColumn(name="circle", referencedColumnName="id")
     */
    private $circle;
    /**
     * @var Friendship $circle
     * @ORM\ManyToOne(targetEntity="Friendship", inversedBy="memberships", cascade={"persist"})
     * @ORM\JoinColumn(name="friendship", referencedColumnName="id")
     */
    private $friendship;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $state;
    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    /**
     * @var ArrayCollection<Chat> | Collection $chats
     * @ORM\OneToMany(targetEntity="Chat", mappedBy="member", cascade={"persist"})
     */
    private $chats;
    
    /**
     * @return string
     */
    public function getId(): string
    {
      return $this->id;
    }
    
    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
      $this->id = $id;
    }
    
    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
      return $this->date;
    }
    
    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
      $this->date = $date;
    }
    
    /**
     * @return Friendship
     */
    public function getFriendship(): Friendship
    {
      return $this->friendship;
    }
    
    /**
     * @param Friendship $friendship
     */
    public function setFriendship(Friendship $friendship): void
    {
      $this->friendship = $friendship;
    }
    
    /**
     * @return int
     */
    public function getState(): int
    {
      return $this->state;
    }
    
    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
      $this->state = $state;
    }
    
    /**
     * @return Circle
     */
    public function getCircle(): Circle
    {
      return $this->circle;
    }
    
    /**
     * @param Circle $circle
     */
    public function setCircle(Circle $circle): void
    {
      $this->circle = $circle;
    }
    
    /**
     * @return ArrayCollection<Chat> | Collection
     */
    public function getChats(): Collection
    {
      return $this->chats;
    }
    
    /**
     * @param ArrayCollection<Chat> | Collection $chats
     */
    public function setChats(ArrayCollection $chats): void
    {
      $this->chats = $chats;
    }
    
    /**
     * @param Chat $chats
     */
    public function addChats(Chat $chats): void
    {
      $this->chats->add($chats);
    }
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->chats = new ArrayCollection();
      $this->date = new DateTime();
    }
    
    /**
     * @return array
     */
    public function toJSON()
    {
      return array(
        'id' => $this->id,
        'circle' => $this->circle->getName(),
        'friendship' => $this->friendship->getId(),
        'date' => $this->getDate(),
      );
    }
    
    /**
     * @param object $m
     * @return Member
     */
    public static function fromJSON(object $m)
    {
      $member = new Member();
      
      if (isset($m->id)) $member->setId($m->id);
      if (isset($m->circle)) $member->setCircle($m->circle);
      if (isset($m->friendship)) $member->setFriendship($m->friendship);
      if (isset($m->date)) $member->setDate($m->date);
      
      return $member;
    }
  }
