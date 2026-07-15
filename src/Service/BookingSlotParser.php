<?php

namespace Webkul\BagistoApi\Service;

use Carbon\Carbon;

class BookingSlotParser
{
    /**
     * Parse a user-friendly time range (e.g. "10:00 AM - 11:00 AM") into Unix timestamps.
     *
     * @return array{from:int,to:int}
     */
    public function parse(string $date, string $timeRange, ?string $timezone = null): array
    {
        $timezone ??= config('app.timezone');

        $range = trim($timeRange);

        $parts = preg_split('/\s*[-–—]\s*/u', $range) ?: [];

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Invalid time range.');
        }

        [$fromTime, $toTime] = [trim($parts[0]), trim($parts[1])];

        $from = $this->parseDateTime($date, $fromTime, $timezone);
        $to = $this->parseDateTime($date, $toTime, $timezone);

        if ($to->lessThanOrEqualTo($from)) {
            $to = $to->addDay();
        }

        return [
            'from' => $from->getTimestamp(),
            'to' => $to->getTimestamp(),
        ];
    }

    private function parseDateTime(string $date, string $time, string $timezone): Carbon
    {
        $candidate = trim($date.' '.$time);

        $formats = [
            'Y-m-d h:i A',
            'Y-m-d g:i A',
            'Y-m-d h:i a',
            'Y-m-d g:i a',
            'Y-m-d h A',
            'Y-m-d g A',
            'Y-m-d h a',
            'Y-m-d g a',
            'Y-m-d H:i',
            'Y-m-d G:i',
            'Y-m-d H',
            'Y-m-d G',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $candidate, $timezone);
            } catch (\Throwable) {
                // try next format
            }
        }

        throw new \InvalidArgumentException('Invalid time format.');
    }
}
