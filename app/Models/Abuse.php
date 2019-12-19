<?php
  
  
  namespace br\Models;
  
  // dependencies
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Abuse
   * @package br\Models
   * @ORM\Entity()
   * @ORM\Table(name="abuse")
   */
  class Abuse
  {
    /**
     * @var string $id
     * @ORM\Id()
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="abuse", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var string $description
     * @ORM\Column(type="text")
     */
    private $description;
    /**
     * @var DateTime $date
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
     * @return User
     */
    public function getUser(): User
    {
      return $this->user;
    }
    
    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
      $this->user = $user;
    }
    
    /**
     * @return string
     */
    public function getDescription(): string
    {
      return $this->description;
    }
    
    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
      $this->description = $description;
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
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'user' => $this->user->toJSON(),
        'description' => $this->description,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $a
     * @return Abuse
     */
    public static function fromJSON($a)
    {
      $abuse = new Abuse();
      if (isset($a->user)) $abuse->setUser($a->user);
      if (isset($a->description)) $abuse->setDescription($a->description);
      if (isset($a->date)) $abuse->setDate($a->date);
      
      return $abuse;
    }
  }
