<?php

namespace App\Models;

use App\Core\Database;

class Session
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM sessions WHERE id = ?');
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        return $session ?: null;
    }

    public static function findByPin(string $pin): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM sessions WHERE code_pin = ?');
        $stmt->execute([$pin]);
        $session = $stmt->fetch();
        return $session ?: null;
    }

    public static function create(int $quizId): array
    {
        $pin = self::generateUniquePin();

        $stmt = Database::connection()->prepare(
            'INSERT INTO sessions (quiz_id, code_pin, statut) VALUES (?, ?, ?)'
        );
        $stmt->execute([$quizId, $pin, 'attente']);

        return self::find((int) Database::connection()->lastInsertId());
    }

    public static function startQuestion(int $sessionId, int $questionId): ?array
    {
        $stmt = Database::connection()->prepare(
            "UPDATE sessions SET statut = 'en_cours', question_courante_id = ?, question_started_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$questionId, $sessionId]);

        return self::find($sessionId);
    }

    public static function finish(int $sessionId): ?array
    {
        $stmt = Database::connection()->prepare(
            "UPDATE sessions SET statut = 'termine', question_courante_id = NULL WHERE id = ?"
        );
        $stmt->execute([$sessionId]);

        return self::find($sessionId);
    }

    public static function leaderboard(int $sessionId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.id, p.numero, p.pseudo, COALESCE(SUM(a.points_obtenus), 0) AS score
             FROM players p
             LEFT JOIN answers a ON a.player_id = p.id
             WHERE p.session_id = ?
             GROUP BY p.id, p.numero, p.pseudo
             ORDER BY score DESC, p.numero ASC'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    private static function generateUniquePin(): string
    {
        do {
            $pin = (string) random_int(100000, 999999);
            $exists = self::findByPin($pin);
        } while ($exists !== null);

        return $pin;
    }
}
