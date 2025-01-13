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

#[ORM\Entity(repositoryClass: SlotRepository::class)]
class Slot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\LessThan(propertyPath: 'dateEnd', message: 'The beginning date must be before the ending date.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'The ending date must be after the beginning date.')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['check' => '(dateEnd > dateDebut)'])]
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
     * 
     */
    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context, SlotRepository $slotRepository): void
    {
        //Check if start is before end
        if ($this->dateEnd <= $this->dateDebut) {
            $context->buildViolation('End date must be after start date')
                ->atPath('dateEnd')
                ->addViolation();
        }

        // Check if start and end are between championchipList's beginDate and endDate 
        $championship = $this->championshipListRepository->findOneBy([
            'id' => $this->championshipList->getId()
        ]);

        if ($championship) {
            $championshipStart = $championship->getDateStart();
            $championshipEnd = $championship->getDateEnd();

            if ($this->dateDebut < $championshipStart || $this->dateEnd > $championshipEnd) {
                $context->buildViolation('Slot dates must be within the championship dates.')
                    ->atPath('dateDebut')
                    ->addViolation();
            }
        }

        // Check if there's no overlap between all slots.
        // Fetch all slots with the same championshipList
        $existingSlots = $slotRepository->findBy(['championshipList' => $this->championshipList]);

        foreach ($existingSlots as $existingSlot) {
            // Skip the current slot if updating an existing one
            if ($this->id && $this->id === $existingSlot->getId()) {
                continue;
            }

            // Check for overlap
            if (($this->dateDebut < $existingSlot->getDateEnd() && $this->dateEnd > $existingSlot->getDateDebut()) ||
                ($this->dateEnd > $existingSlot->getDateEnd() && $this->dateDebut < $existingSlot->getDateEnd)
            ) {
                $context->buildViolation('The slot overlaps with an existing slot.')
                    ->atPath('dateDebut')
                    ->addViolation();
                break;
            }
        }
    }

    public function __construct(ChampionshipListRepository $championshipListRepository)
    {
        $this->encounters = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->championshipListRepository = $championshipListRepository;
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
}
