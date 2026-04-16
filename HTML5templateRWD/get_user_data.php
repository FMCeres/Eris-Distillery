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
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    
    if (empty($email) && empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email or name parameter is required']);
        exit;
    }
    
    $database = readDatabase();
    
    // Find user by email or name
    $user = null;
    foreach ($database['rsvp_data'] as $rsvp) {
        if ((!empty($email) && $rsvp['email'] === $email) || 
            (!empty($name) && strtolower($rsvp['full_name']) === strtolower($name))) {
            $user = $rsvp;
            break;
        }
    }
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user_data' => [
                'name' => $user['full_name'],
                'email' => $user['email'],
                'discount' => $user['discount'],
                'timestamp' => $user['timestamp']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>



