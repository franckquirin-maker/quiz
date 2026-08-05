<?php

namespace App\Services;

class ScoringService
{
    /**
     * Calcule les points en fonction du temps de réponse : dégressif linéaire
     * entre points_max (réponse instantanée) et un plancher de 10% (réponse
     * juste avant l'expiration du temps). Aucun point si la réponse est fausse
     * ou hors délai.
     */
    public static function compute(bool $correcte, int $pointsMax, int $dureeSecondes, int $tempsEcouleMs): int
    {
        if (!$correcte) {
            return 0;
        }

        $dureeMs = $dureeSecondes * 1000;
        if ($tempsEcouleMs >= $dureeMs) {
            return 0;
        }

        $tempsEcouleMs = max(0, $tempsEcouleMs);
        $ratio = 1 - ($tempsEcouleMs / $dureeMs);

        $plancher = 0.1;
        $facteur = $plancher + (1 - $plancher) * $ratio;

        return (int) round($pointsMax * $facteur);
    }
}
