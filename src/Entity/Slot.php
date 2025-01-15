<?php

namespace App\Entity;

use App\Repository\SlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\ChampionshipListRepository;
use App\Validator\SlotDatesConstraint;

#[ORM\Entity(repositoryClass: SlotRepository::class)]
class Slot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[SlotDatesConstraint]
    #[Assert\LessThan(propertyPath: 'dateEnd', message: 'The beginning date must be before the ending date.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[SlotDatesConstraint]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'The ending date must be after the beginning date.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    /**
     * @var Collection<int, Encounter>
     */
    #[ORM\OneToMany(targetEntity: Encounter::class, mappedBy: 'slot')]
    private Collection $encounters;

    #[ORM\ManyToOne(inversedBy: 'slot')]
    private ?ChampionshipList $championshipList = null;

    private ChampionshipListRepository $championshipListRepository;

    #[ORM\Column]
    private ?int $length = null;

    /**
     * @var Collection<int, Team>
     */
    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'slots')]
    private Collection $teams;

    /**
     * @var Collection<int, Championship>
     */
    #[ORM\OneToMany(targetEntity: Championship::class, mappedBy: 'slot')]
    private Collection $championships;

    public function __construct()
    {
        $this->encounters = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->championships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

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
            $encounter->setSlot($this);
        }

        return $this;
    }

    public function removeEncounter(Encounter $encounter): static
    {
        if ($this->encounters->removeElement($encounter)) {
            // set the owning side to null (unless already changed)
            if ($encounter->getSlot() === $this) {
                $encounter->setSlot(null);
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

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        $this->teams->removeElement($team);

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
            $championship->setSlot($this);
        }

        return $this;
    }

    public function removeChampionship(Championship $championship): static
    {
        if ($this->championships->removeElement($championship)) {
            // set the owning side to null (unless already changed)
            if ($championship->getSlot() === $this) {
                $championship->setSlot(null);
            }
        }

        return $this;
    }
}
