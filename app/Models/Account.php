<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;

  /**
   * Class Account
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="accounts")
   */
  class Account
  {
    /**
     * @var User
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accounts", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $name;
    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $date;
    
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
    public function getName(): string
    {
      return $this->name;
    }
    
    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
      $this->name = $name;
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
      $this->date = new DateTime();
    }
  }
