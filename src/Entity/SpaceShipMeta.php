<?php

namespace App\Entity;

use App\Repository\SpaceShipMetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpaceShipMetRepository::class)]
class SpaceShipMeta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descriptionSeo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keywordsSeo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authorSeo = null;

    #[ORM\OneToOne(targetEntity: SpaceShip::class)]
    #[ORM\JoinColumn(name: 'spaceship_id', referencedColumnName: 'id')]
    private SpaceShip|null $spaceship = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescriptionSeo(): ?string
    {
        return $this->descriptionSeo;
    }

    public function setDescriptionSeo(?string $descriptionSeo): static
    {
        $this->descriptionSeo = $descriptionSeo;

        return $this;
    }

    public function getKeywordsSeo(): ?string
    {
        return $this->keywordsSeo;
    }

    public function setKeywordsSeo(?string $keywordsSeo): static
    {
        $this->keywordsSeo = $keywordsSeo;

        return $this;
    }

    public function getAuthorSeo(): ?string
    {
        return $this->authorSeo;
    }

    public function setAuthorSeo(?string $authorSeo): static
    {
        $this->authorSeo = $authorSeo;

        return $this;
    }

    public function getSpaceship(): ?SpaceShip
    {
        return $this->spaceship;
    }

    public function setSpaceship(?SpaceShip $spaceship): static
    {
        $this->spaceship = $spaceship;

        return $this;
    }
}
