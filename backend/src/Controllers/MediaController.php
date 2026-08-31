<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class MediaController
{
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];

    private const MAX_SIZE = 20 * 1024 * 1024; // 20 Mo

    public function upload(Request $request): void
    {
        if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            Response::error('Aucun fichier reçu', 422);
            return;
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::error("Échec du téléversement (code {$file['error']})", 422);
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            Response::error('Fichier trop volumineux (20 Mo maximum)', 422);
            return;
        }

        $mimeType = function_exists('mime_content_type')
            ? mime_content_type($file['tmp_name'])
            : $file['type'];

        if (!isset(self::ALLOWED_TYPES[$mimeType])) {
            Response::error('Type de fichier non autorisé (images ou vidéos uniquement)', 422);
            return;
        }

        $extension = self::ALLOWED_TYPES[$mimeType];
        $filename = bin2hex(random_bytes(8)) . '.' . $extension;
        $destination = APP_ROOT . '/public/media/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            Response::error("Impossible d'enregistrer le fichier sur le serveur", 500);
            return;
        }

        Response::json([
            'media_url' => '/media/' . $filename,
            'media_type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
        ], 201);
    }
}
