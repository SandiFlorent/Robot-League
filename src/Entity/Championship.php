<?php

namespace App\Entity;

use App\Enum\State;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ChampionshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\State as ChampionshipState;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\triggers\triggers;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ChampionshipRepository::class)]
class Championship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'championships')]
    private ?Team $blueTeam = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $greenTeam = null;

    #[Assert\PositiveOrZero(message: 'Le nombre de buts doit être positif ou nul.')]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $blueGoal = null;

    #[Assert\PositiveOrZero(message: 'Le nombre de buts doit être positif ou nul.')]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $greenGoal = null;

    #[ORM\Column(nullable: true, enumType: State::class)]
    private ?State $state = null;

    public function __construct()
    {
        if ($this->state === null) {
            $this->state = State::NOT_STARTED;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlueTeam(): ?Team
    {
        return $this->blueTeam;
    }

    public function setBlueTeam(?Team $blueTeam): static
    {
        $this->blueTeam = $blueTeam;

        return $this;
    }

    public function getGreenTeam(): ?Team
    {
        return $this->greenTeam;
    }

    public function setGreenTeam(?Team $greenTeam): static
    {
        $this->greenTeam = $greenTeam;

        return $this;
    }

    public function getBlueGoal(): ?int
    {
        return $this->blueGoal;
    }

    public function setBlueGoal(?int $blueGoal): static
    {
        $this->blueGoal = $blueGoal;

        return $this;
    }

    public function getGreenGoal(): ?int
    {
        return $this->greenGoal;
    }

    public function setGreenGoal(?int $greenGoal): static
    {
        $this->greenGoal = $greenGoal;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    //Equivalent to SQL triggers
    public function discardGoals(): void
    {
        //We reduce the total goals of the blue and green team
        $this->blueTeam->updateTotalGoals(-$this->blueGoal);
        $this->greenTeam->updateTotalGoals(-$this->greenGoal);

        //We set back this championship's goals to 0
        $this->blueGoal = 0;
        $this->greenGoal = 0;
    }

    public function updateNbEnCounterByOne($difference): void
    {
        $this->blueTeam->updateNbEncounter($difference);
        $this->greenTeam->updateNbEncounter($difference);
    }


    #[ORM\PreUpdate]
    public function beforeUpdateChampionshipTeamGoals(PreUpdateEventArgs $event): void
    {
        // Check if `blue_goal` has changed
        if ($event->hasChangedField('blue_goal')) {
            $oldValue = $event->getOldValue('blue_goal');
            $newValue = $event->getNewValue('blue_goal');
            $difference = $newValue - $oldValue;

            // Update the blue team's goals
            $this->blueTeam->updateTotalGoals($difference);
        }

        // Check if `green_goal` has changed
        if ($event->hasChangedField('green_goal')) {
            $oldValue = $event->getOldValue('green_goal');
            $newValue = $event->getNewValue('green_goal');
            $difference = $newValue - $oldValue;

            // Update the green team's goals
            $this->greenTeam->updateTotalGoals($difference);
        }
    }

    public function beforeUpdateChampionship(PreUpdateEventArgs $event, EntityManagerInterface $entityManager)
    {
        if ($event->hasChangedField('state')) {
            $oldState = $event->getOldValue('state');
            $newState = $event->getNewValue('state');

            $entityManager->beginTransaction();
            try {
                // Create new triggers that will (hopefully) update the teams' stats
                $trigger = new triggers($oldState, $newState, $this->blueTeam, $this->greenTeam, $this->blueGoal, $this->greenGoal);
                $trigger->championshipStateTriggers();

                $entityManager->flush();
                $entityManager->commit();
            } catch (\Exception $e) {
                $entityManager->rollback();
                throw $e;
            }
        }
    }
}
