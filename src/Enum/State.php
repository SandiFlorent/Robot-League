<?php

namespace App\Enum;

enum State: string
{
    case CANCELED = 'annulé';
    case IN_PROGRESS = 'en cours';
    case NOT_STARTED = 'non commencé';
    case WIN_BLUE = "Victoire Blue";
    case WIN_GREEN = "Victoire Green";
    case DRAW = "Égalité";
    case FORFAIT_GREEN = "Forfait vert";
    case FORFAIT_BLUE = "Forfait Bleu";
}
