<?php

namespace App\Entity;

use App\Repository\FieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FieldRepository::class)]
class Field
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    /**
     * @var Collection<int, Encounter>
     */
    #[ORM\OneToMany(targetEntity: Encounter::class, mappedBy: 'field')]
    private Collection $encounters;

    #[ORM\ManyToOne(inversedBy: 'field')]
    private ?ChampionshipList $championshipList = null;

    /**
     * @var Collection<int, Championship>
     */
    #[ORM\OneToMany(targetEntity: Championship::class, mappedBy: 'field')]
    private Collection $championships;

    public function __construct()
    {
        $this->encounters = new ArrayCollection();
        $this->championships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return Collection<int, Encounter>
     */
    public function getEncounters(): Collection
    {
        return $this->encounters;
    }

    public function addEncounter(Encounter $encounter): static
    {
        if (!$this->encounters->contains($encounter)) {
            $this->encounters->add($encounter);
            $encounter->setField($this);
        }

        return $this;
    }

    public function removeEncounter(Encounter $encounter): static
    {
        if ($this->encounters->removeElement($encounter)) {
            // set the owning side to null (unless already changed)
            if ($encounter->getField() === $this) {
                $encounter->setField(null);
            }
        }

        return $this;
    }

    public function getChampionshipList(): ?ChampionshipList
    {
        return $this->championshipList;
    }

    public function setChampionshipList(?ChampionshipList $championshipList): static
    {
        $this->championshipList = $championshipList;

        return $this;
    }

    /**
     * @return Collection<int, Championship>
     */
    public function getChampionships(): Collection
    {
        return $this->championships;
    }

    public function addChampionship(Championship $championship): static
    {
        if (!$this->championships->contains($championship)) {
            $this->championships->add($championship);
            $championship->setField($this);
        }

        return $this;
    }

    public function removeChampionship(Championship $championship): static
    {
        if ($this->championships->removeElement($championship)) {
            // set the owning side to null (unless already changed)
            if ($championship->getField() === $this) {
                $championship->setField(null);
            }
        }

        return $this;
    }
}
