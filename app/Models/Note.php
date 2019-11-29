<?php
  
  
  namespace br\Models;
  
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Note
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="notes")
   */
  class Note
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Task $task
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="notes", cascade={"persist"})
     * @ORM\JoinColumn(name="task", referencedColumnName="id")
     */
    private $task;
    /**
     * @var string $note
     * @ORM\Column(type="text")
     */
    private $note;
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
     * @return string
     */
    public function getNote(): string
    {
      return $this->note;
    }
    
    /**
     * @param string $note
     */
    public function setNote(string $note): void
    {
      $this->note = $note;
    }
    
    /**
     * @return Task
     */
    public function getTask(): Task
    {
      return $this->task;
    }
    
    /**
     * @param Task $task
     */
    public function setTask(Task $task): void
    {
      $this->task = $task;
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
        'task' => $this->task->getId(),
        'note' => $this->note,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $n
     * @return Note
     */
    public static function fromJSON($n)
    {
      $note = new Note();
      if (isset($n->id)) $note->setId($n->id);
      if (isset($n->task)) $note->setTask($n->task);
      if (isset($n->note)) $note->setNote($n->note);
      if (isset($n->date)) $note->setDate($n->date);
      
      return $note;
    }
  }
