<?php

namespace App\Service;

class StatsCalculator
{
    private const TIMEZONE = 'Europe/Paris';
    private const NOT_SENT = 'À envoyer';
    private const INTERVIEW_STATUSES = ['Entretien', 'Offre reçue', 'Acceptée'];
    private const REMINDED_STATUS = 'À relancer';

    public function calculate(array $applications, \DateTimeImmutable $now): array
    {
        $now = $now->setTimezone(new \DateTimeZone(self::TIMEZONE));
        $weekStartDate = $now->modify('monday this week');
        $weekStart = $weekStartDate->format('Y-m-d');
        $weekEnd = $weekStartDate->modify('+6 days')->format('Y-m-d');

        $sent = 0;
        $thisWeek = 0;
        $interviews = 0;
        $reminded = 0;

        foreach ($applications as $application) {
            if ($application['applyDate'] === null || $application['status'] === self::NOT_SENT) {
                continue;
            }

            $sent++;

            $date = $application['applyDate']->format('Y-m-d');
            if ($date >= $weekStart && $date <= $weekEnd) {
                $thisWeek++;
            }

            if (in_array($application['status'], self::INTERVIEW_STATUSES, true)) {
                $interviews++;
            }

            if (in_array(self::REMINDED_STATUS, $application['history'], true)) {
                $reminded++;
            }
        }

        return [
            'sent' => $sent,
            'thisWeek' => $thisWeek,
            'interviews' => $interviews,
            'reminded' => $reminded,
        ];
    }
}