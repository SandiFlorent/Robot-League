<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
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

    public function __construct()
    {
        $this->TeamMembers = new ArrayCollection();
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
}
