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
  
  /** @ORM\Entity @ORM\Table(name="messages") */
  class Message
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;
    /**
     * @var Friendship $friendship
     * @ORM\OneToOne(targetEntity="Friendship", inversedBy="messages")
     * @ORM\JoinColumn(name="friendship", referencedColumnName="id")
     */
    private $friendship;
    
    /**
     * @var User $sender
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="sender", referencedColumnName="id")
     */
    private $sender;
    /**
     * @var User $recipient
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="recipient", referencedColumnName="id")
     */
    private $recipient;
    /**
     * @ORM\Column(type="string")
     */
    private $message;
    /**
     * @var integer $state
     * @ORM\Column(type="string")
     */
    private $state;
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    /**
     * @var Collection<Attaches>
     * @ORM\OneToMany(targetEntity="Attaches", mappedBy="message", cascade={"persist"})
     */
    private $attachments;
    
    /**
     * @return string
     */
    public function getSender()
    {
      return $this->sender;
    }
    
    /**
     * @param string $sender
     */
    public function setSender($sender): void
    {
      $this->sender = $sender;
    }
    
    /**
     * @return string
     */
    public function getRecipient()
    {
      return $this->recipient;
    }
    
    /**
     * @param string $recipient
     */
    public function setRecipient($recipient): void
    {
      $this->recipient = $recipient;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
      return $this->message;
    }
    
    /**
     * @param string $message
     */
    public function setMessage($message): void
    {
      $this->message = $message;
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
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->date = new DateTime();
    }
    
    /**
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'sender' => $this->sender,
        'recipient' => $this->recipient,
        'message' => $this->message,
        'state' => $this->state,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $m
     * @return Message
     */
    public static function fromJSON($m)
    {
      $message = new Message();
      if (isset($m->id)) $message->setId($m->id);
      if (isset($m->sender)) $message->setSender($m->sender);
      if (isset($m->recipient)) $message->setRecipient($m->recipient);
      if (isset($m->message)) $message->setMessage($m->message);
      if (isset($m->state)) $message->setState($m->state);
      if (isset($m->date)) $message->setDate($m->date);
      
      return $message;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getAttachments(): Collection
    {
      return $this->attachments;
    }
    
    /**
     * @param ArrayCollection $attachments
     */
    public function setAttachments(ArrayCollection $attachments): void
    {
      $this->attachments = $attachments;
    }
    
    public function addAttachments(Attaches $attachment): void
    {
      $this->attachments->add($attachment);
    }
  }
