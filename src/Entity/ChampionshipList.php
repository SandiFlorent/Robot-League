<?php

namespace App\Entity;

use App\Repository\ChampionshipListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChampionshipListRepository::class)]
class ChampionshipList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ChampionshipName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    /**
     * @var Collection<int, Team>
     */
    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'championshipList')]
    private Collection $teams;

    /**
     * @var Collection<int, Championship>
     */
    #[ORM\OneToMany(targetEntity: Championship::class, mappedBy: 'championshipList')]
    private Collection $matches;

    #[ORM\Column]
    private ?int $threshold = null;

    /**
     * @var Collection<int, Field>
     */
    #[ORM\OneToMany(targetEntity: Field::class, mappedBy: 'championshipList')]
    private Collection $field;

    /**
     * @var Collection<int, Slot>
     */
    #[ORM\OneToMany(targetEntity: Slot::class, mappedBy: 'championshipList')]
    private Collection $slot;

    /**
     * @var Collection<int, Encounter>
     */
    #[ORM\OneToMany(targetEntity: Encounter::class, mappedBy: 'myChampionshipList')]
    private Collection $encounters;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->field = new ArrayCollection();
        $this->slot = new ArrayCollection();
        $this->encounters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChampionshipName(): ?string
    {
        return $this->ChampionshipName;
    }

    public function setChampionshipName(string $ChampionshipName): static
    {
        $this->ChampionshipName = $ChampionshipName;

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

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

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
            $team->setChampionshipList($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getChampionshipList() === $this) {
                $team->setChampionshipList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Championship>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(Championship $match): static
    {
        if (!$this->matches->contains($match)) {
            $this->matches->add($match);
            $match->setChampionshipList($this);
        }

        return $this;
    }

    public function removeMatch(Championship $match): static
    {
        if ($this->matches->removeElement($match)) {
            // set the owning side to null (unless already changed)
            if ($match->getChampionshipList() === $this) {
                $match->setChampionshipList(null);
            }
        }

        return $this;
    }

    public function getThreshold(): ?int
    {
        return $this->threshold;
    }

    public function setThreshold(int $threshold): static
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * @return Collection<int, Field>
     */
    public function getField(): Collection
    {
        return $this->field;
    }

    public function addField(Field $field): static
    {
        if (!$this->field->contains($field)) {
            $this->field->add($field);
            $field->setChampionshipList($this);
        }

        return $this;
    }

    public function removeField(Field $field): static
    {
        if ($this->field->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getChampionshipList() === $this) {
                $field->setChampionshipList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Slot>
     */
    public function getSlot(): Collection
    {
        return $this->slot;
    }

    public function addSlot(Slot $slot): static
    {
        if (!$this->slot->contains($slot)) {
            $this->slot->add($slot);
            $slot->setChampionshipList($this);
        }

        return $this;
    }

    public function removeSlot(Slot $slot): static
    {
        if ($this->slot->removeElement($slot)) {
            // set the owning side to null (unless already changed)
            if ($slot->getChampionshipList() === $this) {
                $slot->setChampionshipList(null);
            }
        }

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
            $encounter->setMyChampionshipList($this);
        }

        return $this;
    }

    public function removeEncounter(Encounter $encounter): static
    {
        if ($this->encounters->removeElement($encounter)) {
            // set the owning side to null (unless already changed)
            if ($encounter->getMyChampionshipList() === $this) {
                $encounter->setMyChampionshipList(null);
            }
        }

        return $this;
    }
}
