<?php
  
  
  namespace br\Models;
  
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Tag
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="tags")
   */
  class Tag
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;
    /**
     * @var Post $post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="tags", cascade={"persist"})
     * @ORM\JoinColumn(name="post", referencedColumnName="id")
     */
    private $post;
    /**
     * @var string $tag
     * @ORM\Column(type="text")
     */
    private $tag;
    /**
     * @var DateTime $date
     * @ORM\Column(type="datetime")
     */
    private $date;
    
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
    public function getTag(): string
    {
      return $this->tag;
    }
    
    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
      $this->tag = $tag;
    }
    
    /**
     * @return string
     */
    public function getPost(): string
    {
      return $this->post;
    }
    
    /**
     * @param string $post
     */
    public function setPost(string $post): void
    {
      $this->post = $post;
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
        'post' => $this->post->getId(),
        'tag' => $this->tag,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $t
     * @return Tag
     */
    public static function fromJSON($t)
    {
      $tag = new Tag();
      if (isset($t->id)) $tag->setId($t->id);
      if (isset($t->post)) $tag->setPost($t->post);
      if (isset($t->tag)) $tag->setTag($t->tag);
      if (isset($t->date)) $tag->setDate($t->date);
      
      return $tag;
    }
  }
