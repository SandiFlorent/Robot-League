<?php

namespace App\Entity;

use App\Enum\State;
use App\Repository\ChampionshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\State as ChampionshipState;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use App\triggers\triggers;
use Exception;

#[HasLifecycleCallbacks]
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
    private ?int $blueGoal = 0;

    #[Assert\PositiveOrZero(message: 'Le nombre de buts doit être positif ou nul.')]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $greenGoal = 0;

    #[ORM\Column(nullable: true, enumType: State::class)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'matches')]
    private ?ChampionshipList $championshipList = null;

    #[ORM\OneToOne(mappedBy: 'matches', cascade: ['persist', 'remove'])]
    private ?Encounter $encounter = null;

    private TeamRepository $teamRepository;

    public function __construct()
    {
        $this->initializeValues();
    }

    private function initializeValues(): void
    {
        $this->greenGoal = 0;
        $this->blueGoal = 0;
        $this->greenGoal = 0;
        $this->blueGoal = 0;
        $this->state = State::NOT_STARTED;
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

    public function getChampionshipList(): ?ChampionshipList
    {
        return $this->championshipList;
    }

    public function setChampionshipList(?ChampionshipList $championshipList): static
    {
        $this->championshipList = $championshipList;

        return $this;
    }

    public function getEncounter(): ?Encounter
    {
        return $this->encounter;
    }

    public function setEncounter(?Encounter $encounter): static
    {
        // unset the owning side of the relation if necessary
        if ($encounter === null && $this->encounter !== null) {
            $this->encounter->setMatches(null);
        }

        // set the owning side of the relation if necessary
        if ($encounter !== null && $encounter->getMatches() !== $this) {
            $encounter->setMatches($this);
        }

        $this->encounter = $encounter;

        return $this;
    }

    public function discardGoals(): void
    {
        $this->blueTeam->updateTotalGoals(-$this->blueGoal);
        $this->greenTeam->updateTotalGoals(-$this->greenGoal);

        $this->blueGoal = 0;
        $this->greenGoal = 0;
    }

    public function updateNbEnCounterByOne($difference): void
    {
        $this->blueTeam->updateNbEncounter($difference);
        $this->greenTeam->updateNbEncounter($difference);
    }

    // Reinitialize the teamms before deleting the championship
    #[ORM\PreRemove]
    public function beforeRemove(PreRemoveEventArgs $event): void
    {
        $this->blueTeam->reinitialize();
        $this->greenTeam->reinitialize();
    }


    #[ORM\PreUpdate]
    public function beforeUpdate(PreUpdateEventArgs $event): void
    {
        
        $entityManager = $event->getObjectManager();
        static $isPersisted = false;
        $isUpdated = false;

        // Ne rien faire si l'entité a déjà été persistée
        if ($isPersisted) {
            return;
        }

        //dump('Test 1 !!!', $entityManager, $this->blueTeam, $this->greenTeam);
        if ($event->hasChangedField('blueGoal') || $event->hasChangedField('greenGoal')) {
            $this->updateGoals($event);
            //if goals are negative, we set them to 0
            if ($this->blueGoal < 0) {
                dd("wtf");
                $this->blueGoal = 0;
            }
            if ($this->greenGoal < 0) {
                dd("wtf");
                $this->greenGoal = 0;
            }
            $isUpdated = true;
        }

        if ($event->hasChangedField('state')) {
            $this->updateState($event);
            // If the score or the nbEncounter or the nbWin is negative, we set them to 0
            $this->checkIfNegativeValues();
            $isUpdated = true;
        }

        // Persist and flush changes if there were updates
        if ($isUpdated) {
            $this->blueTeam->updateNbEncounter(99);
            $entityManager->persist($this->blueTeam);
            $entityManager->persist($this->greenTeam);
            $isPersisted = true;
            
            $entityManager->flush();
            
        }
    }

    private function updateGoals(PreUpdateEventArgs $event): void
    {
        // Update blue goals
        if ($event->hasChangedField('blueGoal')) {
            $oldValue = $event->getOldValue('blueGoal');
            $newValue = $event->getNewValue('blueGoal');
            $difference = $newValue - $oldValue;

            if ($this->blueTeam) {
                $this->blueTeam->updateTotalGoals($difference);
            }
        }

        // Update green goals
        if ($event->hasChangedField('greenGoal')) {
            $oldValue = $event->getOldValue('greenGoal');
            $newValue = $event->getNewValue('greenGoal');
            $difference = $newValue - $oldValue;

            if ($this->greenTeam) {
                $this->greenTeam->updateTotalGoals($difference);
            }
        }
    }

    private function updateState(PreUpdateEventArgs $event): void
    {
        $oldState = $event->getOldValue('state');
        $newState = $event->getNewValue('state');

        try {
            $trigger = new triggers($this, $oldState, $newState);
            $trigger->championshipStateTriggers();
        } catch (Exception $e) {
            // Handle exception as needed
        }
    }

    private function checkIfNegativeValues(): void
    {
        if ($this->blueTeam->getNbEncounter() < 0) {
            $this->blueTeam->setNbEncounter(0);
        }
        if ($this->blueTeam->getNbWin() < 0) {
            $this->blueTeam->setNbWin(0);
        }
        if ($this->blueTeam->getNbGoals() < 0) {
            $this->blueTeam->setNbGoals(0);
        }
        if ($this->greenTeam->getNbEncounter() < 0) {
            $this->greenTeam->setNbEncounter(0);
        }
        if ($this->greenTeam->getNbWin() < 0) {
            $this->greenTeam->setNbWin(0);
        }
        if ($this->greenTeam->getNbGoals() < 0) {
            $this->greenTeam->setNbGoals(0);
        }
    }
}
