<?php

namespace App\Validator;

use App\Entity\Slot;
use App\Repository\SlotRepository;
use App\Repository\ChampionshipListRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SlotDatesConstraintValidator extends ConstraintValidator
{
    private SlotRepository $slotRepository;
    private ChampionshipListRepository $championshipListRepository;

    public function __construct(SlotRepository $slotRepository, ChampionshipListRepository $championshipListRepository)
    {
        $this->slotRepository = $slotRepository;
        $this->championshipListRepository = $championshipListRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Slot) {
            return;
        }

        /** @var SlotDatesConstraint $constraint */

        // Check if start is before end
        if ($value->getDateEnd() <= $value->getDateDebut()) {
            $this->context->buildViolation($constraint->messageOrder)
                ->atPath('dateEnd')
                ->addViolation();
        }

        // Check if dates are within the championship dates
        $championship = $value->getChampionshipList();
        if ($championship) {
            $championshipStart = $championship->getDateStart();
            $championshipEnd = $championship->getDateEnd();

            if ($value->getDateDebut() < $championshipStart || $value->getDateEnd() > $championshipEnd) {
                $this->context->buildViolation($constraint->messageChampionship)
                    ->atPath('dateDebut')
                    ->addViolation();
            }
        }

        // Check if there's no overlap between slots
        $existingSlots = $this->slotRepository->findOverlappingSlots(
            $value->getDateDebut(),
            $value->getDateEnd(),
            $value->getChampionshipList()
        );

        foreach ($existingSlots as $existingSlot) {
            // Skip the current slot if updating an existing one
            if ($value->getId() && $value->getId() === $existingSlot->getId()) {
                continue;
            }

            // Add violation if overlap is found
            $this->context->buildViolation($constraint->messageOverlap)
                ->atPath('dateDebut')
                ->addViolation();
            break;
        }
    }
}
