<?php
  
  namespace br\Models;
  // models
  
  // dependencies
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /** @ORM\Entity  @ORM\Table(name="attaches") */
  class Attaches
  {
    /**
     * @ORM\Id @ORM\Column(type="string")
     */
    private $id;
    /**
     * @var Message $message
     * @ORM\ManyToOne(targetEntity="Message")
     * @ORM\JoinColumn(name="message", referencedColumnName="id")
     */
    private $message;
    /**
     * @ORM\Column(type="string")
     */
    private $type;
    /**
     * @ORM\Column(type="blob")
     */
    private $attachment;
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->date = new DateTime();
    }
    
    /**
     * @return string
     */
    public function getId()
    {
      return $this->id;
    }
    
    /**
     * @param string $id
     */
    public function setId($id): void
    {
      $this->id = $id;
    }
    
    /**
     * @return Message
     */
    public function getMessage()
    {
      return $this->message;
    }
    
    /**
     * @param Message $message
     */
    public function setMessage($message): void
    {
      $this->message = $message;
    }
    
    /**
     * @return string
     */
    public function getAttachment()
    {
      return $this->attachment;
    }
    
    /**
     * @param string $attachment
     */
    public function setAttachment($attachment): void
    {
      $this->attachment = $attachment;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
      return $this->type;
    }
    
    /**
     * @param string $type
     */
    public function setType($type): void
    {
      $this->type = $type;
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
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'message' => $this->message,
        'type' => $this->type,
        'attachment' => $this->attachment,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $a
     * @return Attaches
     */
    public static function fromJSON($a)
    {
      $attachment = new Attaches();
      if (isset($a->id)) $attachment->setId($a->id);
      if (isset($a->attachment)) $attachment->setAttachment($a->attachment);
      if (isset($a->message)) $attachment->setMessage($a->message);
      if (isset($a->type)) $attachment->setType($a->type);
      if (isset($a->date)) $attachment->setDate($a->date);
      
      return $attachment;
    }
    
  }
