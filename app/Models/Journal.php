<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Journal
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="journals")
   */
  class Journal
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $user
     * @ORM\OneToOne(targetEntity="User", inversedBy="journal", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var bool $viewing
     * @ORM\Column(type="boolean")
     */
    private $viewing;
    /**
     * @var DateTime $date
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    /**
     * @var ArrayCollection<Entry>
     * @ORM\OneToMany(targetEntity="Entry", mappedBy="journal", cascade={"persist"})
     */
    private $entries;
    
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
     * @return bool
     */
    public function isViewing(): bool
    {
      return $this->viewing;
    }
    
    /**
     * @param bool $viewing
     */
    public function setViewing(bool $viewing): void
    {
      $this->viewing = $viewing;
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
     * @return ArrayCollection<Entry> | Collection<Entry>
     */
    public function getEntries(): Collection
    {
      return $this->entries;
    }
    
    /**
     * @param ArrayCollection $entries
     */
    public function setEntries(ArrayCollection $entries): void
    {
      $this->entries = $entries;
    }
    
    /**
     * @param Entry $entry
     */
    public function addEntry(Entry $entry): void
    {
      $this->entries->add($entry);
    }
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->entries = new ArrayCollection();
      $this->date = new DateTime();
    }
    
    /**
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'user' => $this->user->getId(),
        'viewing' => $this->viewing,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $j
     * @return Journal
     */
    public static function fromJSON($j)
    {
      $journal = new Journal();
      if (isset($j->id)) $journal->setId($j->id);
      if (isset($j->user)) $journal->setUser($j->user);
      if (isset($j->viewing)) $journal->setViewing($j->viewing);
      if (isset($j->date)) $journal->setDate($j->date);
      
      return $journal;
    }
    
  }
