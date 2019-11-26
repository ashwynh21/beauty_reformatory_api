<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Emotion
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="emotions")
   */
  class Emotion
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="emotions", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var double $mood
     * @ORM\Column(type="float")
     */
    private $mood;
    /**
     * @var DateTime $date
     * @ORM\Column(type="datetime")
     */
    private $date;
    
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
     * @return float
     */
    public function getMood(): float
    {
      return $this->mood;
    }
    
    /**
     * @param float $mood
     */
    public function setMood(float $mood): void
    {
      $this->mood = $mood;
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
      return (object)array(
        'id' => $this->id,
        'user' => $this->user->getId(),
        'mood' => $this->mood,
        'date' => $this->date,
      );
    }
    
    /**
     * @param object $e
     * @return Emotion
     */
    public static function fromJSON(object $e)
    {
      $emotion = new Emotion();
      
      if (isset($e->id)) $emotion->setId($e->id);
      if (isset($e->user)) $emotion->setUser($e->user);
      if (isset($e->mood)) $emotion->setMood($e->mood);
      if (isset($e->date)) $emotion->setDate($e->date);
      
      return $emotion;
    }
  }
