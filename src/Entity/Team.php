<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeInterface;
use DateTime;


#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $Name = null;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\ManyToMany(targetEntity: Member::class, inversedBy: 'teams')]
    private Collection $TeamMembers;

    /**
     * @var Collection<int, Championship>
     */
    #[ORM\OneToMany(targetEntity: Championship::class, mappedBy: 'blueTeam')]
    private Collection $championships;


    #[ORM\Column]
    #[Assert\PositiveOrZero(message: 'Le nombre de buts doit être positif ou nul.')]
    #[ORM\Column( type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $totalPoints = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    #[ORM\Column( type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?float $score = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    #[ORM\Column( type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $nbEncounter = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    #[ORM\Column( type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $nbGoals = 0;

    #[ORM\Column(type: "datetime")]
    private ?DateTimeInterface $inscriptionDate;


    #[ORM\Column]
    #[Assert\PositiveOrZero()]
    #[ORM\Column( type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $nbWin = 0;

    #[ORM\Column]
    private ?bool $isAccepted = null;

    #[ORM\ManyToOne(inversedBy: 'teams')]
    private ?ChampionshipList $championshipList = null;

    #[ORM\ManyToOne(inversedBy: 'myTeams')]
    private ?User $creator = null;

    public function __construct()
    {
        $this->TeamMembers = new ArrayCollection();
        $this->championships = new ArrayCollection();
        $this->inscriptionDate = new DateTime();
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
     * @return Collection<int, Member>
     */
    public function getTeamMembers(): Collection
    {
        return $this->TeamMembers;
    }

    public function addTeamMember(Member $teamMember): static
    {
        if (!$this->TeamMembers->contains($teamMember)) {
            $this->TeamMembers->add($teamMember);
        }

        return $this;
    }

    public function removeTeamMember(Member $teamMember): static
    {
        $this->TeamMembers->removeElement($teamMember);

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
            $championship->setBlueTeam($this);
        }

        return $this;
    }

    public function removeChampionship(Championship $championship): static
    {
        if ($this->championships->removeElement($championship)) {
            // set the owning side to null (unless already changed)
            if ($championship->getBlueTeam() === $this) {
                $championship->setBlueTeam(null);
            }
        }

        return $this;
    }


    public function getTotalPoints(): ?int
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(int $totalPoints): static
    {
        $this->totalPoints = $totalPoints;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getNbEncounter(): ?int
    {
        return $this->nbEncounter;
    }

    public function setNbEncounter(int $nbEncounter): static
    {
        $this->nbEncounter = $nbEncounter;

        return $this;
    }

    public function getNbGoals(): ?int
    {
        return $this->nbGoals;
    }

    public function setNbGoals(int $nbGoals): static
    {
        $this->nbGoals = $nbGoals;

        return $this;
    }

    public function getInscriptionDate(): ?\DateTimeInterface
    {
        return $this->inscriptionDate;
    }

    public function setInscriptionDate(\DateTimeInterface $inscriptionDate): static
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }

    public function getNbWin(): ?int
    {
        return $this->nbWin;
    }

    public function setNbWin(int $nbWin): static
    {
        $this->nbWin = $nbWin;
        return $this;
    }

    public function isAccepted(): ?bool
    {
        return $this->isAccepted;
    }

    public function setAccepted(bool $isAccepted): static
    {
        $this->isAccepted = $isAccepted;

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

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }
}
