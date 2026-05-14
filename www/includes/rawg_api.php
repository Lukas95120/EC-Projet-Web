<?php
require_once __DIR__ . '/config.php';

function rawgRequest(string $endpoint): ?array
{
    $apiKey = RAWG_API_KEY;

    $url = 'https://api.rawg.io/api/' . ltrim($endpoint, '/');
    $separator = str_contains($url, '?') ? '&' : '?';

    $url .= $separator . 'key=' . urlencode($apiKey);

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    return is_array($data) ? $data : null;
}

function searchRawgGames(string $query): array
{
    $query = trim($query);

    if ($query === '') {
        return [];
    }

    $data = rawgRequest(
        'games?search=' . urlencode($query) . '&page_size=12'
    );

    if (!$data || empty($data['results'])) {
        return [];
    }

    return array_filter(
        $data['results'],
        fn ($game) => !isBlockedRawgGame($game)
    );
}

function getRawgGame(int $rawgId): ?array
{
    if ($rawgId <= 0) {
        return null;
    }

    return rawgRequest('games/' . $rawgId);
}

function isBlockedRawgGame(array $game): bool
{
    $blockedWords = [
        'porn',
        'hentai',
        'sex',
        'nsfw',
        'adult',
        'erotic',
        '18+',
        'xxx',
        'nude',
        'naked',
        'futa',
        'milf',
        'boobs',
        'onlyfans',
        'cum',
        'bdsm',
        'rape',
        'fetish',
        'incest',
        'waifu',
        'lewd'
    ];

    $text = strtolower(
        ($game['name'] ?? '') . ' ' .
        ($game['slug'] ?? '') . ' ' .
        ($game['description_raw'] ?? '')
    );

    foreach ($blockedWords as $word) {
        if (str_contains($text, strtolower($word))) {
            return true;
        }
    }

    return false;
}

function extractRawgPlatforms(array $game): string
{
    $platforms = [];

    if (!empty($game['platforms'])) {
        foreach ($game['platforms'] as $platform) {
            if (!empty($platform['platform']['name'])) {
                $platforms[] = $platform['platform']['name'];
            }
        }
    }

    return implode(', ', array_unique($platforms));
}

function extractRawgGenres(array $game): string
{
    $genres = [];

    if (!empty($game['genres'])) {
        foreach ($game['genres'] as $genre) {
            if (!empty($genre['name'])) {
                $genres[] = $genre['name'];
            }
        }
    }

    return implode(', ', array_unique($genres));
}

function extractRawgPublishers(array $game): string
{
    $publishers = [];

    if (!empty($game['publishers'])) {
        foreach ($game['publishers'] as $publisher) {
            if (!empty($publisher['name'])) {
                $publishers[] = $publisher['name'];
            }
        }
    }

    return implode(', ', array_unique($publishers));
}

function extractRawgReleaseYear(array $game): ?int
{
    if (empty($game['released'])) {
        return null;
    }

    return (int)date('Y', strtotime($game['released']));
}