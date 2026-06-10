<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action']) || !isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$action = $data['action'];
$id = $data['id'];

$file = __DIR__ . '/../data/analytics.json';

// Initialize analytics array if it doesn't exist
$analytics = [];
if (file_exists($file)) {
    $content = file_get_contents($file);
    if ($content) {
        $analytics = json_decode($content, true) ?: [];
    }
}

$found = false;
foreach ($analytics as &$stat) {
    if ($stat['id'] === $id) {
        if ($action === 'view') {
            $stat['views'] = ($stat['views'] ?? 0) + 1;
        } elseif ($action === 'order') {
            $stat['orders'] = ($stat['orders'] ?? 0) + 1;
        }
        $found = true;
        break;
    }
}

// Add new product stat if not found
if (!$found) {
    $newStat = ['id' => $id, 'views' => 0, 'orders' => 0];
    if ($action === 'view') {
        $newStat['views'] = 1;
    } elseif ($action === 'order') {
        $newStat['orders'] = 1;
    }
    $analytics[] = $newStat;
}

// Save back to file
if (file_put_contents($file, json_encode($analytics, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
}
