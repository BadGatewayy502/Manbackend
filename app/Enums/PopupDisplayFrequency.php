<?php

namespace App\Enums;

enum PopupDisplayFrequency: string
{
    case OncePerSession = 'once_per_session';
    case OncePerDay = 'once_per_day';
    case EveryVisit = 'every_visit';
}
