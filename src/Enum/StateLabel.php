<?php

namespace App\Enum;

enum StateLabel: string
{
    case Created = 'created';
    case Open = 'open';
    case Closed = 'closed';
    case Ongoing = 'ongoing';
    case Past = 'past';
    case Cancelled = 'cancelled';

    public static function getAllowedStates(): array
    {
        return [
            self::Created,
            self::Open,
            self::Closed,
            self::Cancelled,
        ];
    }
}
