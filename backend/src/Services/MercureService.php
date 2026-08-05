<?php

namespace App\Services;

class MercureService
{
    /**
     * Publie une mise à jour sur le topic d'une session pour que les joueurs
     * et l'écran hôte se resynchronisent en temps réel (SSE via Mercure).
     */
    public static function publish(int $sessionId, string $event, array $data): void
    {
        $hubUrl = getenv('MERCURE_INTERNAL_URL') ?: 'http://mercure/.well-known/mercure';
        $secret = getenv('MERCURE_JWT_SECRET') ?: 'changeme-mercure-secret';

        $topic = self::topic($sessionId);

        $payload = json_encode(array_merge(['event' => $event], $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $token = self::publisherToken($secret, [$topic]);

        $ch = curl_init($hubUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'topic' => $topic,
                'data' => $payload,
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public static function topic(int $sessionId): string
    {
        return "session/{$sessionId}";
    }

    private static function publisherToken(string $secret, array $topics): string
    {
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body = self::base64UrlEncode(json_encode(['mercure' => ['publish' => $topics]]));
        $signature = self::base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

        return "{$header}.{$body}.{$signature}";
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
