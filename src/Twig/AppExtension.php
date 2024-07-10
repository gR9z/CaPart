<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_duration', [$this, 'formatDurationFilter']),
        ];
    }

    public function formatDurationFilter($duration): string
    {
        if ($duration >= 60) {
            $hours = floor($duration / 60);
            $minutes = $duration % 60;

            $hoursText = $hours == 1 ? 'hour' : 'hours';
            $minutesText = $minutes == 1 ? 'minute' : 'minutes';

            if ($minutes > 0) {
                return sprintf('%d %s and %d %s', $hours, $hoursText, $minutes, $minutesText);
            } else {
                return sprintf('%d %s', $hours, $hoursText);
            }
        } else {
            $minutesText = $duration == 1 ? 'minute' : 'minutes';
            return sprintf('%d %s', $duration, $minutesText);
        }
    }
}