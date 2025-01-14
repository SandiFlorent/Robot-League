<?php

namespace App\Entity;

use App\Enum\State;
use App\Repository\ChampionshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\State as ChampionshipState;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

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
    private ?int $blueGoal = null;

    #[Assert\PositiveOrZero(message: 'Le nombre de buts doit être positif ou nul.')]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $greenGoal = null;

    #[ORM\Column(nullable: true, enumType: State::class)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'matches')]
    private ?ChampionshipList $championshipList = null;

    #[ORM\OneToOne(mappedBy: 'matches', cascade: ['persist', 'remove'])]
    private ?Encounter $encounter = null;

    private TeamRepository $teamRepository;

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

    #[ORM\PreUpdate]
    public function beforeUpdateGoals(PreUpdateEventArgs $event): void
    {
        // Obtenir l'EntityManager depuis l'événement
        $entityManager = $event->getObjectManager();
        static $isPersisted = false; // Utilisation d'un flag statique pour éviter des persistance en boucle infinie
    
        // Ne rien faire si l'entité a déjà été persistée
        if ($isPersisted) {
            return;
        }
    
        $isUpdated = false;
    
        // Mettre à jour les buts de l'équipe bleue si nécessaire
        if ($event->hasChangedField('blueGoal')) {
            $oldValue = $event->getOldValue('blueGoal');
            $newValue = $event->getNewValue('blueGoal');
            $difference = $newValue - $oldValue;
    
            if ($this->blueTeam) {
                $this->blueTeam->updateTotalGoals($difference);
                $isUpdated = true;
            }
        }
    
        // Mettre à jour les buts de l'équipe verte si nécessaire
        if ($event->hasChangedField('greenGoal')) {
            $oldValue = $event->getOldValue('greenGoal');
            $newValue = $event->getNewValue('greenGoal');
            $difference = $newValue - $oldValue;
    
            if ($this->greenTeam) {
                $this->greenTeam->updateTotalGoals($difference);
                $isUpdated = true;
            }
        }
    
        // Si des modifications ont eu lieu, persistez les changements
        if ($isUpdated) {
            // Marquer comme persisté
            $isPersisted = true;
    
            $entityManager->persist($this->blueTeam);
            $entityManager->persist($this->greenTeam);
            $entityManager->flush();  // Effectuer un flush pour sauvegarder les modifications
        }
    }
}