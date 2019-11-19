<?php
  
  
  namespace br\Models;
  
  // helpers
  // models
  
  // dependencies
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /** @ORM\Entity @ORM\Table(name="friendships") */
  class Friendship
  {
    /**
     * @ORM\Id
     * @ORM\Column(type="string", name="id",)
     */
    private $id;
    /**
     * @var User $initiated
     * @ORM\ManyToOne(targetEntity="User", inversedBy="initiated", cascade={"persist"})
     * @ORM\JoinColumn(name="initiator", referencedColumnName="id")
     */
    private $initiator;
    /**
     * @var User $subjected
     * @ORM\ManyToOne(targetEntity="User", inversedBy="subjected", cascade={"persist"})
     * @ORM\JoinColumn(name="subject", referencedColumnName="id")
     */
    private $subject;
    /**
     * can be 0 = pending, 1 = approved, 2 = declined, 3 = blocked
     * @ORM\Column(type="integer")
     */
    private $state;
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    /**
     * @return User
     */
    public function getInitiator()
    {
      return $this->initiator;
    }
    
    /**
     * @param User $initiator
     */
    public function setInitiator($initiator): void
    {
      $this->initiator = $initiator;
    }
    
    /**
     * @return User
     */
    public function getSubject()
    {
      return $this->subject;
    }
    
    /**
     * @param User $subject
     */
    public function setSubject($subject): void
    {
      $this->subject = $subject;
    }
    
    /**
     * @return integer
     */
    public function getState()
    {
      return $this->state;
    }
    
    /**
     * @param integer $state
     */
    public function setState($state): void
    {
      $this->state = $state;
    }
    
    /**
     * @return DateTime
     */
    public function getDate()
    {
      return $this->date;
    }
    
    /**
     * @param DateTime $date
     */
    public function setDate($date): void
    {
      $this->date = $date;
    }
    
    /**
     * @return Collection<Message>
     */
    public function getMessages(): Collection
    {
      return $this->messages;
    }
    
    /**
     * @param ArrayCollection<Message> $messages
     */
    public function setMessages(ArrayCollection $messages): void
    {
      $this->messages = $messages;
    }
    
    public function addMessage(Message $message): void
    {
      $this->messages->add($message);
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
      return $this->id;
    }
    
    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
      $this->id = $id;
    }
    
    /*
     * ORM variables go here
     */
    
    /**
     * @var Collection<Message> $messages
     * @ORM\OneToMany(targetEntity="Message", mappedBy="friendship", cascade={"persist"})
     */
    private $messages;
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->messages = new ArrayCollection();
      $this->date = new DateTime();
    }
    
    /**
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'initiator' => $this->initiator,
        'subject' => $this->subject,
        'status' => $this->state,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $f
     * @return Friendship
     */
    public static function fromJSON($f)
    {
      $friends = new Friendship();
      if (isset($f->initiator)) $friends->setInitiator($f->initiator);
      if (isset($f->subject)) $friends->setSubject($f->subject);
      if (isset($f->status)) $friends->setState($f->status);
      if (isset($f->date)) $friends->setDate($f->date);
      
      return $friends;
    }
  }
