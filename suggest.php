<?php
header('Content-Type: application/json');

// 1) Get the query string
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode([]);
    exit;
}

// 2) Load your JSON file
// Use dirname(__FILE__) instead of __DIR__ for compatibility
$basePath = dirname(__FILE__);
$jsonPath = $basePath . '/data/books.json';

if (!file_exists($jsonPath)) {
    // If file missing, return empty array
    echo json_encode([]);
    exit;
}

$books = json_decode(file_get_contents($jsonPath), true);
if (!is_array($books)) {
    echo json_encode([]);
    exit;
}

// 3) Filter for titles that start with the query (case-insensitive)
$qLower = mb_strtolower($q);
$maxResults = 10;
$suggestions = [];

foreach ($books as $b) {
    // Normalize to lowercase
    $titleLower = mb_strtolower($b['title']);

    // Prefix match on title only
    if (strpos($titleLower, $qLower) === 0) {
        $suggestions[] = $b['title']; // Only include the title
        if (count($suggestions) >= $maxResults) {
            break;
        }
    }
}

// 4) Return JSON array of suggestions
echo json_encode($suggestions);