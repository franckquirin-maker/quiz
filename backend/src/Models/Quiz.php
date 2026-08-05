<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Quiz
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM quizzes ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM quizzes WHERE id = ?');
        $stmt->execute([$id]);
        $quiz = $stmt->fetch();
        return $quiz ?: null;
    }

    public static function withQuestions(int $id): ?array
    {
        $quiz = self::find($id);
        if (!$quiz) {
            return null;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM questions WHERE quiz_id = ? ORDER BY ordre ASC');
        $stmt->execute([$id]);
        $quiz['questions'] = $stmt->fetchAll();

        return $quiz;
    }

    public static function create(string $nom, ?string $description, string $statut = 'brouillon'): array
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO quizzes (nom, description, statut) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nom, $description, $statut]);

        return self::find((int) Database::connection()->lastInsertId());
    }

    public static function update(int $id, string $nom, ?string $description, string $statut): ?array
    {
        $stmt = Database::connection()->prepare(
            'UPDATE quizzes SET nom = ?, description = ?, statut = ? WHERE id = ?'
        );
        $stmt->execute([$nom, $description, $statut, $id]);

        return self::find($id);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM quizzes WHERE id = ?');
        $stmt->execute([$id]);
    }
}
