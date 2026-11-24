<?php

namespace App\Service;

class ActionAlertCalculator
{
    private const TIMEZONE = 'Europe/Paris';

    private const FOLLOW_UP_DELAY_DAYS = 7;
    private const MAX_FOLLOW_UPS = 2;
    private const ABANDON_DELAY_DAYS = 5;
    private const OFFER_RESPONSE_DELAY_DAYS = 10;

    private const STATUS_TO_SEND = 'À envoyer';
    private const STATUS_SENT = 'Envoyée';
    private const STATUS_TO_FOLLOW_UP = 'À relancer';
    private const STATUS_INTERVIEW = 'Entretien';
    private const STATUS_OFFER = 'Offre reçue';

    public function calculate(array $applications, \DateTimeImmutable $now): array
    {
        $today = $this->day($now->setTimezone(new \DateTimeZone(self::TIMEZONE)));
        $alerts = [];

        foreach ($applications as $application) {
            $next = $this->nextAction($application, $today);
            if ($next === null) {
                continue;
            }

            $alerts[] = [
                'applicationId' => $application['applicationId'],
                'jobTitle' => $application['jobTitle'],
                'company' => $application['company'],
                'action' => $next['action'],
                'dueDate' => $next['dueDate']->format('Y-m-d'),
                'overdueDays' => $next['dueDate'] < $today ? (int) $next['dueDate']->diff($today)->days : 0,
            ];
        }

        usort($alerts, fn (array $a, array $b) => strcmp($a['dueDate'], $b['dueDate']));

        return $alerts;
    }

    public function nextAction(array $application, \DateTimeImmutable $today): ?array
    {
        $history = $application['history'];
        if ($history === []) {
            return null;
        }

        usort($history, fn (array $a, array $b) => $a['date'] <=> $b['date']);
        $currentIndex = array_key_last($history);
        $current = $history[$currentIndex];
        $statusDay = $this->day($current['date']);

        return match ($current['status']) {
            self::STATUS_TO_SEND => [
                'action' => 'Postuler',
                'dueDate' => $today,
            ],
            self::STATUS_SENT, self::STATUS_TO_FOLLOW_UP => $this->followUpAction($application, $history, $currentIndex, $today),
            self::STATUS_INTERVIEW => $statusDay >= $today
                ? ['action' => 'Préparer l’entretien', 'dueDate' => $statusDay]
                : ['action' => 'Relancer après l’entretien', 'dueDate' => $statusDay->modify('+' . self::FOLLOW_UP_DELAY_DAYS . ' days')],
            self::STATUS_OFFER => [
                'action' => 'Répondre à l’offre',
                'dueDate' => $statusDay->modify('+' . self::OFFER_RESPONSE_DELAY_DAYS . ' days'),
            ],
            default => null,
        };
    }

    private function followUpAction(array $application, array $history, int $currentIndex, \DateTimeImmutable $today): array
    {
        $done = 0;
        $last = $this->day($application['applyDate']);

        foreach ($history as $index => $entry) {
            if ($index === $currentIndex || $entry['status'] !== self::STATUS_TO_FOLLOW_UP) {
                continue;
            }

            $done++;
            $day = $this->day($entry['date']);
            if ($day > $last) {
                $last = $day;
            }
        }

        if ($done >= self::MAX_FOLLOW_UPS) {
            return [
                'action' => 'Abandonner la candidature',
                'dueDate' => $last->modify('+' . self::ABANDON_DELAY_DAYS . ' days'),
            ];
        }

        if ($history[$currentIndex]['status'] === self::STATUS_TO_FOLLOW_UP) {
            return [
                'action' => 'Relancer',
                'dueDate' => $this->day($history[$currentIndex]['date']),
            ];
        }

        return [
            'action' => 'Relancer',
            'dueDate' => $last->modify('+' . self::FOLLOW_UP_DELAY_DAYS . ' days'),
        ];
    }

    private function day(\DateTimeInterface $date): \DateTimeImmutable
    {
        return new \DateTimeImmutable($date->format('Y-m-d'), new \DateTimeZone(self::TIMEZONE));
    }
}