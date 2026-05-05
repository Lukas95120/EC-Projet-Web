<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$title = trim($_GET['title'] ?? '');

if ($title === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Titre manquant.'
    ]);
    exit;
}

if (!defined('RAWG_API_KEY') || RAWG_API_KEY === '' || RAWG_API_KEY === 'TA_CLE_API_RAWG_ICI') {
    echo json_encode([
        'success' => false,
        'message' => 'Clé API RAWG manquante dans config.php.'
    ]);
    exit;
}

$searchUrl = 'https://api.rawg.io/api/games?key=' . urlencode(RAWG_API_KEY)
    . '&search=' . urlencode($title)
    . '&page_size=1';

$response = @file_get_contents($searchUrl);

if ($response === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Impossible de contacter l’API.'
    ]);
    exit;
}

$data = json_decode($response, true);

if (empty($data['results'][0])) {
    echo json_encode([
        'success' => false,
        'message' => 'Aucun jeu trouvé.'
    ]);
    exit;
}

$game = $data['results'][0];
$publisher = '';

if (!empty($game['id'])) {
    $detailsUrl = 'https://api.rawg.io/api/games/' . urlencode($game['id'])
        . '?key=' . urlencode(RAWG_API_KEY);

    $detailsResponse = @file_get_contents($detailsUrl);

    if ($detailsResponse !== false) {
        $details = json_decode($detailsResponse, true);
        $publisher = $details['publishers'][0]['name'] ?? '';
    }
}

echo json_encode([
    'success' => true,
    'game' => [
        'title' => $game['name'] ?? '',
        'release_year' => !empty($game['released']) ? substr($game['released'], 0, 4) : '',
        'genre' => $game['genres'][0]['name'] ?? '',
        'platform' => $game['platforms'][0]['platform']['name'] ?? '',
        'publisher' => $publisher,
        'critic_score' => isset($game['metacritic']) ? round($game['metacritic'] / 10, 1) : '',
        'user_score' => isset($game['rating']) ? round($game['rating'] * 2, 1) : '',
        'global_sales' => '',
        'image_url' => $game['background_image'] ?? ''
    ]
]);
exit;