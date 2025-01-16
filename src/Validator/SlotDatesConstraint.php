<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
final class SlotDatesConstraint extends Constraint
{
    public string $messageOverlap = 'The slot overlaps with an existing slot.';
    public string $messageChampionship = 'Slot dates must be within the championship dates.';
    public string $messageOrder = 'End date must be after start date.';

    // You can keep the constructor as it is
    public function __construct(
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }

    // The validatedBy method points to the Validator class
    public function validatedBy(): string
    {
        return static::class . 'Validator';
    }
}
