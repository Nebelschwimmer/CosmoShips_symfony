<?php

namespace App\Entity;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\SpaceShipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Dto\SpaceshipDto;


#[ORM\Entity(repositoryClass: SpaceShipRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SpaceShip
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(nullable: true)]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: SpaceShipCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private SpaceShipCategory|null $category = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User|null $user = null;

    #[ORM\JoinTable(name: 'likes_to_spaceships')]
    #[ORM\JoinColumn(name: 'spaceship_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'like_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Like', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ArrayCollection|PersistentCollection $likes;

    #[ORM\ManyToOne(inversedBy: 'spaceship', targetEntity: Publisher::class, cascade: ['persist'])]
    private ?Publisher $publisher = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct(UserInterface|User $user = null)
    {
        $this->user = $user;
        $this->likes = new ArrayCollection();
    }

    public static function createFromDto(UserInterface|User $user, SpaceShipDto $dto): SpaceShip
    {
        $spacehip = new self($user);

        $spacehip->setName($dto->name)
            ->setDescription($dto->description)
            ->setImage($dto->image);

        return $spacehip;
    }
    public static function updateFromDto(SpaceShip $spaceShip, SpaceShipCategory $category, SpaceShipDto $dto): SpaceShip
    {
        $spaceShip
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setCategory($category)
            ->setImage($dto->image);

        return $spaceShip;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?SpaceShipCategory
    {
        return $this->category;
    }

    public function setCategory(?SpaceShipCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user->getId();

        return $this;
    }

    public function getLikes(): ArrayCollection|PersistentCollection
    {
        return $this->likes;
    }

    public function setLikes(ArrayCollection|PersistentCollection $likes): static
    {
        $this->likes = $likes;

        return $this;
    }
    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
        }

        return $this;
    }
    public function removeLike(Like $like): static
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
        }

        return $this;
    }

    public function getPublisher(): ?Publisher
    {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

}