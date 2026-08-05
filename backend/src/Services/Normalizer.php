<?php

namespace App\Services;

class Normalizer
{
    /**
     * Normalise une chaîne pour comparaison : minuscule, sans accents,
     * sans apostrophes/espaces/ponctuation (seuls lettres et chiffres restent).
     * Ex: "Grey's Anatomy" / "greys anatomy" / "grey s anatomy" -> "greysanatomy"
     */
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        return preg_replace('/[^a-z0-9]/', '', $value) ?? '';
    }
}
