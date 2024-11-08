<?php

namespace App\Entity;

use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublisherRepository::class)]
class Publisher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?int $userId = null;

    /**
     * @var Collection<int, SpaceShip>
     */
    #[ORM\OneToMany(targetEntity: SpaceShip::class, mappedBy: 'publisher', orphanRemoval: true)]
    private Collection $spaceship;

    public function __toString(): string
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->spaceship = new ArrayCollection();
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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, SpaceShip>
     */
    public function getSpaceship(): Collection
    {
        return $this->spaceship;
    }

    public function addSpaceship(SpaceShip $spaceship): static
    {
        if (!$this->spaceship->contains($spaceship)) {
            $this->spaceship->add($spaceship);
            $spaceship->setPublisher($this);
        }

        return $this;
    }

    public function removeSpaceship(SpaceShip $spaceship): static
    {
        if ($this->spaceship->removeElement($spaceship)) {
            // set the owning side to null (unless already changed)
            if ($spaceship->getPublisher() === $this) {
                $spaceship->setPublisher(null);
            }
        }

        return $this;
    }
}
