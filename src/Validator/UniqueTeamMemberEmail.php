<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueTeamMemberEmail extends Constraint
{
    public string $message = 'alerts.duplicateMemberEmail';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
