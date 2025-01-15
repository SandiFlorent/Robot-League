<?php

namespace App\Enum;

enum State: string
{
    case CANCELED = 'canceled';
    case IN_PROGRESS = 'inProgress';
    case NOT_STARTED = 'notStarted';
    case WIN_BLUE = "winBlue";
    case WIN_GREEN = "winGreen";
    case DRAW = "draw";
    case FORFAIT_GREEN = "forfaitGreen";
    case FORFAIT_BLUE = "forfaitBlue";

    //ToDo : Add a trad
    case FORFAIT = "forfait";
}
