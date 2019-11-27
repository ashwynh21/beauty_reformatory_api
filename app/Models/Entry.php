<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Entry
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="entries")
   */
  class Entry
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Journal $journal
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="entries", cascade={"persist"})
     * @ORM\JoinColumn(name="journal", referencedColumnName="id")
     */
    private $journal;
    /**
     * @var string $entry
     * @ORM\Column(type="text")
     */
    private $entry;
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
    public function getEntry(): string
    {
      return $this->entry;
    }
    
    /**
     * @param string $entry
     */
    public function setEntry(string $entry): void
    {
      $this->entry = $entry;
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
      return (object)[
        'id' => $this->id,
        'journal' => $this->journal->getId(),
        'entry' => $this->entry,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $e
     * @return Entry
     */
    public static function fromJSON($e)
    {
      $entry = new Entry();
      if (isset($e->id)) $entry->setId($e->id);
      if (isset($e->journal)) $entry->setJournal($e->journal);
      if (isset($e->entry)) $entry->setEntry($e->entry);
      if (isset($e->date)) $entry->setDate($e->date);
      
      return $entry;
    }
  }
