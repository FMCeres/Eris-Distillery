<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database file path
$databaseFile = 'database.json';

// Function to read database
function readDatabase() {
    global $databaseFile;
    if (file_exists($databaseFile)) {
        $json = file_get_contents($databaseFile);
        return json_decode($json, true);
    }
    return ['rsvp_data' => [], 'last_id' => 0];
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $database = readDatabase();
    
    echo json_encode([
        'success' => true,
        'rsvp_data' => $database['rsvp_data']
    ]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>



