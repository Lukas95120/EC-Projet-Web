<?php

function normalizeModerationText(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');

    $replacements = [
        '0' => 'o',
        '1' => 'i',
        '!' => 'i',
        '3' => 'e',
        '4' => 'a',
        '@' => 'a',
        '5' => 's',
        '$' => 's',
        '7' => 't',
        '+' => 't',
        '8' => 'b'
    ];

    $text = strtr($text, $replacements);

    $text = preg_replace('/[\s\-_\.]+/u', '', $text);
    $text = preg_replace('/(.)\1{2,}/u', '$1', $text);

    return $text;
}

function containsBlockedContent(string $text): bool
{
    $blockedWords = [
        'pute',
        'salope',
        'connard',
        'connasse',
        'encule',
        'fdp',
        'nazi',
        'hitler',
        'negro',
        'negre',
        'raciste',
        'viol',
        'porno',
        'porn',
        'sexe',
        'sexuel'
    ];

    $normalizedText = normalizeModerationText($text);

    foreach ($blockedWords as $word) {
        if (str_contains($normalizedText, normalizeModerationText($word))) {
            return true;
        }
    }

    return false;
}

function isSpamLikeContent(string $text): bool
{
    $text = trim($text);

    if (mb_strlen($text, 'UTF-8') > 1200) {
        return true;
    }

    if (preg_match('/(.)\1{8,}/u', $text)) {
        return true;
    }

    return false;
}

function validateUserContent(string $text): ?string
{
    if (containsBlockedContent($text)) {
        return 'Ton message contient du contenu inapproprié.';
    }

    if (isSpamLikeContent($text)) {
        return 'Ton message semble trop long ou répétitif.';
    }

    return null;
}