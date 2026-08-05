<?php

namespace App\Models;

use App\Core\Database;

class Answer
{
    public static function findForPlayerAndQuestion(int $playerId, int $questionId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM answers WHERE player_id = ? AND question_id = ?'
        );
        $stmt->execute([$playerId, $questionId]);
        $answer = $stmt->fetch();
        return $answer ?: null;
    }

    public static function record(
        int $playerId,
        int $questionId,
        string $reponseBrute,
        string $reponseNormalisee,
        int $tempsMs,
        int $pointsObtenus,
        bool $correcte
    ): array {
        $stmt = Database::connection()->prepare(
            'INSERT INTO answers (player_id, question_id, reponse_brute, reponse_normalisee, temps_ms, points_obtenus, correcte)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$playerId, $questionId, $reponseBrute, $reponseNormalisee, $tempsMs, $pointsObtenus, $correcte]);

        return self::findForPlayerAndQuestion($playerId, $questionId);
    }
}
