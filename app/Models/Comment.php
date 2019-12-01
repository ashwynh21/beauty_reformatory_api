<?php
  
  
  namespace br\Models;
  
  use DateTime;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Comment
   * @package br\Models
   * @ORM\Entity
   * @ORM\Table(name="comments")
   */
  class Comment
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $user
     * @ORM\OneToOne(targetEntity="User", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;
    /**
     * @var Post $post
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="post", referencedColumnName="id")
     */
    private $post;
    /**
     * @var string $text
     * @ORM\Column(type="text")
     */
    private $text;
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
    public function getText(): string
    {
      return $this->text;
    }
    
    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
      $this->text = $text;
    }
    
    /**
     * @return Post
     */
    public function getPost(): Post
    {
      return $this->post;
    }
    
    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
      $this->post = $post;
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
        'user' => $this->user->getId(),
        'post' => $this->post->getId(),
        'text' => $this->text,
        'date' => $this->date
      ];
    }
    
    /**
     * @param object $c
     * @return Comment
     */
    public static function fromJSON($c)
    {
      $comment = new Comment();
      if (isset($c->id)) $comment->setId($c->id);
      if (isset($c->user)) $comment->setUser($c->post);
      if (isset($c->post)) $comment->setPost($c->post);
      if (isset($c->text)) $comment->setText($c->tag);
      if (isset($c->date)) $comment->setDate($c->date);
      
      return $comment;
    }
  }
