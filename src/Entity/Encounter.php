<?php

namespace App\Entity;

use App\Repository\EncounterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EncounterRepository::class)]
class Encounter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'encounters')]
    private ?Field $field = null;

    #[ORM\ManyToOne(inversedBy: 'encounters')]
    private ?Slot $slot = null;

    #[ORM\OneToOne(inversedBy: 'encounter', cascade: ['persist'])]
    #[ORM\Column(nullable: true)]
    private ?Championship $matches = null;

    #[ORM\ManyToOne(inversedBy: 'encounters')]
    private ?ChampionshipList $myChampionshipList = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): ?Field
    {
        return $this->field;
    }

    public function setField(?Field $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getSlot(): ?Slot
    {
        return $this->slot;
    }

    public function setSlot(?Slot $slot): static
    {
        $this->slot = $slot;

        return $this;
    }

    public function getMatches(): ?Championship
    {
        return $this->matches;
    }

    public function setMatches(?Championship $matches): static
    {
        $this->matches = $matches;

        return $this;
    }

    public function getMyChampionshipList(): ?ChampionshipList
    {
        return $this->myChampionshipList;
    }

    public function setMyChampionshipList(?ChampionshipList $myChampionshipList): static
    {
        $this->myChampionshipList = $myChampionshipList;

        return $this;
    }
}
