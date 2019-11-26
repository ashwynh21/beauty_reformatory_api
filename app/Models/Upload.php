<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Uploads
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="uploads")
   */
  class Upload
  {
    /**
     * @ORM\Id @ORM\Column(type="string")
     */
    private $id;
    /**
     * @var Chat $chat
     * @ORM\ManyToOne(targetEntity="Chat")
     * @ORM\JoinColumn(name="chat", referencedColumnName="id")
     */
    private $chat;
    /**
     * @ORM\Column(type="string")
     */
    private $type;
    /**
     * @var resource $upload
     * @ORM\Column(type="blob")
     */
    private $upload;
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
     * @return Chat
     */
    public function getChat()
    {
      return $this->chat;
    }
    
    /**
     * @param Chat $chat
     */
    public function setChat($chat): void
    {
      $this->chat = $chat;
    }
    
    /**
     * @return resource
     */
    public function getUpload()
    {
      return $this->upload;
    }
    
    /**
     * @param string $upload
     */
    public function setUpload($upload): void
    {
      $this->upload = $upload;
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
        'chat' => $this->chat->getId(),
        'type' => $this->type,
        'upload' => $this->upload,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $u
     * @return Upload
     */
    public static function fromJSON($u)
    {
      $upload = new Upload();
      if (isset($u->id)) $upload->setId($u->id);
      if (isset($u->upload)) $upload->setUpload($u->attachment);
      if (isset($u->chat)) $upload->setChat($u->chat);
      if (isset($u->type)) $upload->setType($u->type);
      if (isset($u->date)) $upload->setDate($u->date);
      
      return $upload;
    }
  }
