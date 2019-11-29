<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Daily
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="daily")
   */
  class Daily
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var Journal $journal
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="dailies", cascade={"persist"})
     * @ORM\JoinColumn(name="journal", referencedColumnName="id")
     */
    private $journal;
    /**
     * @var string $description
     * @ORM\Column(type="text")
     */
    private $description;
    /**
     * @var DateTime $time
     * @ORM\Column(type="datetime")
     */
    private $time;
    /**
     * @var DateTime $duration
     * @ORM\Column(type="datetime")
     */
    private $duration;
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
    public function getDuration(): DateTime
    {
      return $this->duration;
    }
    
    /**
     * @param DateTime $duration
     */
    public function setDuration(DateTime $duration): void
    {
      $this->duration = $duration;
    }
    
    /**
     * @return DateTime
     */
    public function getTime(): DateTime
    {
      return $this->time;
    }
    
    /**
     * @param DateTime $time
     */
    public function setTime(DateTime $time): void
    {
      $this->time = $time;
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
        'time' => $this->time,
        'duration' => $this->time,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $d
     * @return Daily
     */
    public static function fromJSON($d)
    {
      $daily = new Daily();
      if (isset($d->id)) $daily->setId($d->id);
      if (isset($d->journal)) $daily->setJournal($d->journal);
      if (isset($d->description)) $daily->setDescription($d->description);
      if (isset($d->time)) $daily->setTime($d->time);
      if (isset($d->duration)) $daily->setDuration($d->duration);
      if (isset($d->date)) $daily->setDate($d->date);
      
      return $daily;
    }
  }
