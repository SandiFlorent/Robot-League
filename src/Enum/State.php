<?php

namespace App\Enum;

enum State: string
{
    case Canceled = 'annulé';
    case InProgress = 'en cours';
    case NotStarted = 'non commencé';
    case WinBlue = "Victoire Blue";
    case WinGreen = "Victoire Green";
    case Draw = "Égalité";
}
