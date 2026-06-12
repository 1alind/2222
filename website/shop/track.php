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
$dailyFile = __DIR__ . '/../data/daily.json';

// --- Global Analytics ---
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
        } elseif ($action === 'click') {
            $stat['clicks'] = ($stat['clicks'] ?? 0) + 1;
        } elseif ($action === 'swipe') {
            $stat['swipes'] = ($stat['swipes'] ?? 0) + 1;
        } elseif ($action === 'whatsapp') {
            $stat['whatsapp'] = ($stat['whatsapp'] ?? 0) + 1;
        } elseif ($action === 'time_spent') {
            $stat['duration'] = ($stat['duration'] ?? 0) + ($data['duration'] ?? 0);
        }
        $found = true;
        break;
    }
}

if (!$found) {
    $newStat = ['id' => $id, 'views' => 0, 'orders' => 0, 'clicks' => 0, 'swipes' => 0, 'whatsapp' => 0, 'duration' => 0];
    if ($action === 'view') {
        $newStat['views'] = 1;
    } elseif ($action === 'order') {
        $newStat['orders'] = 1;
    } elseif ($action === 'click') {
        $newStat['clicks'] = 1;
    } elseif ($action === 'swipe') {
        $newStat['swipes'] = 1;
    } elseif ($action === 'whatsapp') {
        $newStat['whatsapp'] = 1;
    } elseif ($action === 'time_spent') {
        $newStat['duration'] = $data['duration'] ?? 0;
    }
    $analytics[] = $newStat;
}
file_put_contents($file, json_encode($analytics, JSON_PRETTY_PRINT));


// --- Daily Analytics ---
$daily = [];
if (file_exists($dailyFile)) {
    $content = file_get_contents($dailyFile);
    if ($content) {
        $daily = json_decode($content, true) ?: [];
    }
}

$today = date('Y-m-d');
if (!isset($daily[$today])) {
    $daily[$today] = [];
}

if (!isset($daily[$today][$id])) {
    $daily[$today][$id] = ['views' => 0, 'orders' => 0, 'clicks' => 0, 'swipes' => 0, 'whatsapp' => 0, 'duration' => 0];
}

if ($action === 'view') {
    $daily[$today][$id]['views']++;
} elseif ($action === 'order') {
    $daily[$today][$id]['orders']++;
} elseif ($action === 'click') {
    $daily[$today][$id]['clicks']++;
} elseif ($action === 'swipe') {
    $daily[$today][$id]['swipes'] = ($daily[$today][$id]['swipes'] ?? 0) + 1;
} elseif ($action === 'whatsapp') {
    $daily[$today][$id]['whatsapp'] = ($daily[$today][$id]['whatsapp'] ?? 0) + 1;
} elseif ($action === 'time_spent') {
    $daily[$today][$id]['duration'] = ($daily[$today][$id]['duration'] ?? 0) + ($data['duration'] ?? 0);
}

file_put_contents($dailyFile, json_encode($daily, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
