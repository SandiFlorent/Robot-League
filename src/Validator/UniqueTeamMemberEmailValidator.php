<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use App\Entity\Team;

class UniqueTeamMemberEmailValidator extends ConstraintValidator
{
    public function validate($team, Constraint $constraint): void
{
    if (!$constraint instanceof UniqueTeamMemberEmail) {
        throw new UnexpectedTypeException($constraint, UniqueTeamMemberEmail::class);
    }

    if (!$team instanceof Team) {
        throw new UnexpectedValueException($team, Team::class);
    }

    $emails = [];
    foreach ($team->getTeamMembers() as $member) {
        $email = $member->getEmail();

        // Ensure the email is not null or empty
        if (empty($email)) {
            continue; // Skip validation for empty emails
        }

        // Normalize the email (trim whitespace, convert to lowercase)
        $normalizedEmail = strtolower(trim($email));

        if (in_array($normalizedEmail, $emails, true)) {
            $this->context->buildViolation($constraint->message)
                ->atPath('TeamMembers') // Adjust path if needed
                ->addViolation();
            return; // Stop further validation after finding a duplicate
        }

        $emails[] = $normalizedEmail;
    }
}

}