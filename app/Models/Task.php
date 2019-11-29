<?php
  
  
  namespace br\Models;
  
  
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Task
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="tasks")
   */
  class Task
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Journal $journal
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="tasks", cascade={"persist"})
     * @ORM\JoinColumn(name="journal", referencedColumnName="id")
     */
    private $journal;
    /**
     * @var string $title
     * @ORM\Column(type="text")
     */
    private $title;
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
    /**
     * @var ArrayCollection<Note> $notes
     * @ORM\OneToMany(targetEntity="Note", mappedBy="task", cascade={"persist"})
     */
    private $notes;
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->notes = new ArrayCollection();
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
     * @return string
     */
    public function getTitle(): string
    {
      return $this->title;
    }
    
    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
      $this->title = $title;
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
     * @return ArrayCollection<Note> | Collection
     */
    public function getNotes(): Collection
    {
      return $this->notes;
    }
    
    /**
     * @param Note $notes
     */
    public function setNotes(Note $notes): void
    {
      $this->notes = $notes;
    }
    
    /**
     * @param Note $note
     */
    public function addNote(Note $note): void
    {
      $this->notes->add($note);
    }
    
    /**
     * @return object
     */
    public function toJSON()
    {
      return (object)[
        'id' => $this->id,
        'journal' => $this->journal->getId(),
        'title' => $this->title,
        'description' => $this->description,
        'completed' => $this->completed,
        'due' => $this->due,
        'finish' => $this->finish,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $t
     * @return Task
     */
    public static function fromJSON($t)
    {
      $task = new Task();
      if (isset($t->id)) $task->setId($t->id);
      if (isset($t->journal)) $task->setJournal($t->journal);
      if (isset($t->title)) $task->setTitle($t->title);
      if (isset($t->description)) $task->setDescription($t->description);
      if (isset($t->completed)) $task->setCompleted($t->completed);
      if (isset($t->due)) $task->setDue($t->due);
      if (isset($t->finish)) $task->setFinish($t->finish);
      if (isset($t->date)) $task->setDate($t->date);
      
      return $task;
    }
    
  }
