<?php

namespace App\Models;

use App\Core\Database;
use PDOException;

class Player
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM players WHERE id = ?');
        $stmt->execute([$id]);
        $player = $stmt->fetch();
        return $player ?: null;
    }

    public static function forSession(int $sessionId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM players WHERE session_id = ? ORDER BY numero ASC');
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    /**
     * Crée un joueur avec un numéro séquentiel façon Squid Game (001, 002, ...).
     * Retente en cas de collision sur une inscription concurrente.
     */
    public static function join(int $sessionId, ?string $pseudo = null): array
    {
        $attempts = 0;

        while (true) {
            $attempts++;

            $stmt = Database::connection()->prepare(
                'SELECT COUNT(*) AS total FROM players WHERE session_id = ?'
            );
            $stmt->execute([$sessionId]);
            $count = (int) $stmt->fetch()['total'];
            $numero = str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);

            try {
                $insert = Database::connection()->prepare(
                    'INSERT INTO players (session_id, numero, pseudo) VALUES (?, ?, ?)'
                );
                $insert->execute([$sessionId, $numero, $pseudo]);
                return self::find((int) Database::connection()->lastInsertId());
            } catch (PDOException $e) {
                if ($attempts >= 5) {
                    throw $e;
                }
                // Collision sur le numéro (double inscription concurrente) : on retente.
            }
        }
    }
}
