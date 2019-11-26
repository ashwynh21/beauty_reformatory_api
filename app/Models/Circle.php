<?php
  
  
  namespace br\Models;
  
  // dependencies
  use DateTime;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;
  use Doctrine\ORM\Mapping as ORM;
  use Exception;
  
  /**
   * Class Circle
   * @ORM\Entity
   * @ORM\Table(name="circles")
   */
  class Circle
  {
    /**
     * @var string $id
     * @ORM\Id
     * @ORM\Column(type="text")
     */
    private $id;
    /**
     * @var User $creator
     * @ORM\ManyToOne(targetEntity="User", inversedBy="circles", cascade={"persist"})
     * @ORM\JoinColumn(name="creator", referencedColumnName="id")
     */
    private $creator;
    /**
     * @var string $name
     * @ORM\Column(type="text")
     */
    private $name;
    /**
     * @var string $cover
     * @ORM\Column(type="text")
     */
    private $cover;
    /**
     * @var int $status
     * @ORM\Column(type="integer")
     */
    private $status;
    /**
     * @var DateTime $date
     * @ORM\Column(type="datetime")
     */
    private $date;
    /**
     * @var ArrayCollection<Member> $members
     * @ORM\OneToMany(targetEntity="Member", mappedBy="circle", cascade={"persist"})
     */
    private $members;
    
    public function __construct()
    {
      try {
        $this->id = md5(random_bytes(64));
      } catch (Exception $e) {
      }
      $this->members = new ArrayCollection();
      $this->date = new DateTime();
    }
    
    /**
     * @return ArrayCollection<Member> | Collection<Member>
     */
    public function getMembers(): Collection
    {
      return $this->members;
    }
    
    /**
     * @param ArrayCollection<Member> $members
     */
    public function setMembers(ArrayCollection $members): void
    {
      $this->members = $members;
    }
    
    /**
     * @param Member $member
     */
    public function addMember(Member $member)
    {
      $this->members->add($member);
    }
    
    /**
     * @param Member $member
     * @return bool
     */
    public function removeMember(Member $member): bool
    {
      return $this->members->removeElement($member);
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
     * @return int
     */
    public function getStatus(): int
    {
      return $this->status;
    }
    
    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
      $this->status = $status;
    }
    
    /**
     * @return string
     */
    public function getCover(): string
    {
      return $this->cover;
    }
    
    /**
     * @param string $cover
     */
    public function setCover(string $cover): void
    {
      $this->cover = $cover;
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
     * @return User
     */
    public function getCreator(): User
    {
      return $this->creator;
    }
    
    /**
     * @param User $creator
     */
    public function setCreator(User $creator): void
    {
      $this->creator = $creator;
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
      return (object)array(
        'id' => $this->id,
        'creator' => $this->creator->getEmail(),
        'name' => $this->name,
        'cover' => $this->cover,
        'date' => $this->date,
      );
    }
    
    /**
     * @param object $c
     * @return Circle
     */
    public static function fromJSON(object $c)
    {
      $circle = new Circle();
      if (isset($c->id)) $circle->setId($c->id);
      if (isset($c->creator)) $circle->setCreator($c->creator);
      if (isset($c->name)) $circle->setName($c->name);
      if (isset($c->cover)) $circle->setCover($c->cover);
      if (isset($c->status)) $circle->setStatus($c->status);
      if (isset($c->date)) $circle->setDate($c->date);
      
      return $circle;
    }
  }
