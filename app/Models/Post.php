<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Post
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="posts")
   */
  class Post
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $user
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var string $title
     * @ORM\Column(type="text")
     */
    private $title;
    /**
     * @var string $content
     * @ORM\Column(type="text")
     */
    private $content;
    /**
     * @var DateTime $date
     * @ORM\Column(type="datetime")
     */
    private $date;
    /**
     * @var ArrayCollection | Collection $comments
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post", cascade={"persist"})
     */
    private $comments;
    /**
     * @var Tag $tags
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="tags", cascade={"persist"})
     */
    private $tags;
    
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
    public function getContent(): string
    {
      return $this->content;
    }
    
    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
      $this->content = $content;
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
     * @return object
     */
    public function toJSON()
    {
      
      return (object)[
        'id' => $this->id,
        'user' => $this->user->getId(),
        'title' => $this->title,
        'content' => $this->content,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $p
     * @return Post
     */
    public static function fromJSON($p)
    {
      $post = new Post();
      if (isset($p->id)) $post->setId($p->id);
      if (isset($p->user)) $post->setUser($p->user);
      if (isset($p->title)) $post->setTitle($p->title);
      if (isset($p->content)) $post->setContent($p->content);
      if (isset($p->date)) $post->setDate($p->date);
      
      return $post;
    }
    
    /**
     * @return Tag
     */
    public function getTags(): Tag
    {
      return $this->tags;
    }
    
    /**
     * @param Tag $tags
     */
    public function setTags(Tag $tags): void
    {
      $this->tags = $tags;
    }
    
    /**
     * @return ArrayCollection|Collection
     */
    public function getComments()
    {
      return $this->comments;
    }
    
    /**
     * @param ArrayCollection|Collection $comments
     */
    public function setComments($comments): void
    {
      $this->comments = $comments;
    }
  }
