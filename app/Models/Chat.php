<?php
  
  
  namespace br\Models;
  
  // dependencies
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Chat
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="chats")
   */
  class Chat
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Member $member
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="chats", cascade={"persist"})
     * @ORM\JoinColumn(name="member", referencedColumnName="id")
     */
    private $member;
    /**
     * @var string $message
     * @ORM\Column(type="text")
     */
    private $message;
    /**
     * @var int $state
     * @ORM\Column(type="integer")
     */
    private $state;
    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $date;
    /**
     * @var ArrayCollection<Upload> $uploads
     * @ORM\OneToMany(targetEntity="Upload", mappedBy="chat", cascade={"persist"})
     */
    private $uploads;
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->uploads = new ArrayCollection();
      $this->date = new DateTime();
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
    public function setId(string $id)
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
     * @return string
     */
    public function getMessage(): string
    {
      return $this->message;
    }
    
    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
      $this->message = $message;
    }
    
    /**
     * @return Member
     */
    public function getMember(): Member
    {
      return $this->member;
    }
    
    /**
     * @param Member $member
     */
    public function setMember(Member $member): void
    {
      $this->member = $member;
    }
    
    /**
     * @return Collection<Upload>
     */
    public function getUploads(): Collection
    {
      return $this->uploads;
    }
    
    /**
     * @param ArrayCollection<Upload> $uploads
     */
    public function setUploads(ArrayCollection $uploads): void
    {
      $this->uploads = $uploads;
    }
    
    /**
     * @param Upload $upload
     */
    public function addUpload(Upload $upload): void
    {
      $this->uploads->add($upload);
    }
    
    /**
     * @return object
     */
    public function toJSON()
    {
      return (object)array(
        'id' => $this->id,
        'member' => $this->member->getId(),
        'message' => $this->message,
        'state' => $this->state,
        'date' => $this->date,
      );
    }
    
    /**
     * @param object $m
     * @return Chat
     */
    public static function fromJSON(object $m)
    {
      $chats = new Chat();
      
      if (isset($m->id)) $chats->setId($m->id);
      if (isset($m->member)) $chats->setMember($m->member);
      if (isset($m->message)) $chats->setMessage($m->message);
      if (isset($m->state)) $chats->setState($m->state);
      if (isset($m->date)) $chats->setDate($m->date);
      
      return $chats;
    }
    
  }
