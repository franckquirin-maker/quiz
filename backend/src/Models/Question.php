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
     * Version de la question sans les réponses, pour diffusion aux joueurs.
     */
    public static function publicView(array $question): array
    {
        unset($question['reponse_normalisee'], $question['reponse_affichee'], $question['reponses_alternatives']);
        return $question;
    }

    /**
     * Retourne la liste des réponses normalisées acceptées (réponse principale + alternatives).
     */
    public static function acceptedAnswers(array $question): array
    {
        $accepted = [$question['reponse_normalisee']];

        if (!empty($question['reponses_alternatives'])) {
            $alternatives = json_decode($question['reponses_alternatives'], true) ?: [];
            foreach ($alternatives as $alternative) {
                $accepted[] = Normalizer::normalize($alternative);
            }
        }

        return array_values(array_unique(array_filter($accepted, fn ($a) => $a !== '')));
    }

    public static function create(array $data): array
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO questions (quiz_id, media_url, media_type, texte, reponse_normalisee, reponse_affichee, reponses_alternatives, points_max, duree_secondes, ordre)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['quiz_id'],
            $data['media_url'] ?? null,
            $data['media_type'],
            $data['texte'] ?? null,
            Normalizer::normalize($data['reponse']),
            $data['reponse'],
            self::encodeAlternatives($data['alternatives'] ?? []),
            $data['points_max'],
            $data['duree_secondes'],
            $data['ordre'] ?? 0,
        ]);

        return self::find((int) Database::connection()->lastInsertId());
    }

    public static function update(int $id, array $data): ?array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE questions SET media_url = ?, media_type = ?, texte = ?, reponse_normalisee = ?, reponse_affichee = ?, reponses_alternatives = ?, points_max = ?, duree_secondes = ?, ordre = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['media_url'] ?? null,
            $data['media_type'],
            $data['texte'] ?? null,
            Normalizer::normalize($data['reponse']),
            $data['reponse'],
            self::encodeAlternatives($data['alternatives'] ?? []),
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

    private static function encodeAlternatives(array $alternatives): ?string
    {
        $cleaned = array_values(array_filter(array_map('trim', $alternatives), fn ($a) => $a !== ''));
        return $cleaned ? json_encode($cleaned, JSON_UNESCAPED_UNICODE) : null;
    }
}
