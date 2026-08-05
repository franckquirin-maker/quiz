<?php

namespace App\Models;

use App\Core\Database;
use App\Services\Normalizer;

class Question
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM questions WHERE id = ?');
        $stmt->execute([$id]);
        $question = $stmt->fetch();
        return $question ?: null;
    }

    public static function forQuiz(int $quizId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM questions WHERE quiz_id = ? ORDER BY ordre ASC');
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    /**
     * Version de la question sans la réponse, pour diffusion aux joueurs.
     */
    public static function publicView(array $question): array
    {
        unset($question['reponse_normalisee'], $question['reponse_affichee']);
        return $question;
    }

    public static function create(array $data): array
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO questions (quiz_id, media_url, media_type, texte, reponse_normalisee, reponse_affichee, points_max, duree_secondes, ordre)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['quiz_id'],
            $data['media_url'] ?? null,
            $data['media_type'],
            $data['texte'] ?? null,
            Normalizer::normalize($data['reponse']),
            $data['reponse'],
            $data['points_max'],
            $data['duree_secondes'],
            $data['ordre'] ?? 0,
        ]);

        return self::find((int) Database::connection()->lastInsertId());
    }

    public static function update(int $id, array $data): ?array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE questions SET media_url = ?, media_type = ?, texte = ?, reponse_normalisee = ?, reponse_affichee = ?, points_max = ?, duree_secondes = ?, ordre = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['media_url'] ?? null,
            $data['media_type'],
            $data['texte'] ?? null,
            Normalizer::normalize($data['reponse']),
            $data['reponse'],
            $data['points_max'],
            $data['duree_secondes'],
            $data['ordre'] ?? 0,
            $id,
        ]);

        return self::find($id);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM questions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
