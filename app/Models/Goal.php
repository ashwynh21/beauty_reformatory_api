<?php
  
  
  namespace br\Models;
  
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Task
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="goals")
   */
  class Goal
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Journal $journal
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="goals", cascade={"persist"})
     * @ORM\JoinColumn(name="journal", referencedColumnName="id")
     */
    private $journal;
    /**
     * @var string $description
     * @ORM\Column(type="text")
     */
    private $description;
    /**
     * @var boolean $title
     * @ORM\Column(type="boolean")
     */
    private $completed;
    /**
     * @var DateTime $due
     * @ORM\Column(type="datetime")
     */
    private $due;
    /**
     * @var DateTime $finish
     * @ORM\Column(type="datetime")
     */
    private $finish;
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
     * @return DateTime
     */
    public function getFinish(): DateTime
    {
      return $this->finish;
    }
    
    /**
     * @param DateTime $finish
     */
    public function setFinish(DateTime $finish): void
    {
      $this->finish = $finish;
    }
    
    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
      return $this->completed;
    }
    
    /**
     * @param bool $completed
     */
    public function setCompleted(bool $completed): void
    {
      $this->completed = $completed;
    }
    
    /**
     * @return DateTime
     */
    public function getDue(): DateTime
    {
      return $this->due;
    }
    
    /**
     * @param DateTime $due
     */
    public function setDue(DateTime $due): void
    {
      $this->due = $due;
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
     * @return Journal
     */
    public function getJournal(): Journal
    {
      return $this->journal;
    }
    
    /**
     * @param Journal $journal
     */
    public function setJournal(Journal $journal): void
    {
      $this->journal = $journal;
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
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'journal' => $this->journal->getId(),
        'description' => $this->description,
        'completed' => $this->completed,
        'due' => $this->due,
        'finish' => $this->finish,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $g
     * @return Goal
     */
    public static function fromJSON($g)
    {
      $goal = new Goal();
      if (isset($g->id)) $goal->setId($g->id);
      if (isset($g->journal)) $goal->setJournal($g->journal);
      if (isset($g->description)) $goal->setDescription($g->description);
      if (isset($g->completed)) $goal->setCompleted($g->completed);
      if (isset($g->due)) $goal->setDue($g->due);
      if (isset($g->finish)) $goal->setFinish($g->finish);
      if (isset($g->date)) $goal->setDate($g->date);
      
      return $goal;
    }
  }
