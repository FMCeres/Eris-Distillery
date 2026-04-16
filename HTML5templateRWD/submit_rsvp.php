<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Function to write database
function writeDatabase($data) {
    global $databaseFile;
    
    // Ensure directory exists and is writable
    $dir = dirname($databaseFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Check if file is writable (or if file doesn't exist, check if directory is writable)
    if (file_exists($databaseFile)) {
        if (!is_writable($databaseFile)) {
            error_log("Database file is not writable: " . $databaseFile);
            return false;
        }
    } else {
        if (!is_writable($dir)) {
            error_log("Database directory is not writable: " . $dir);
            return false;
        }
    }
    
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    if ($jsonData === false) {
        error_log("Failed to encode JSON data: " . json_last_error_msg());
        return false;
    }
    
    $result = file_put_contents($databaseFile, $jsonData, LOCK_EX);
    if ($result === false) {
        error_log("Failed to write to database file: " . $databaseFile);
    }
    
    return $result;
}

// Function to generate discount
function generateDiscount() {
    $discounts = [20, 30, 40];
    return $discounts[array_rand($discounts)];
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
        exit;
    }
    
    // Debug logging (remove in production)
    error_log("Raw input: " . $rawInput);
    error_log("Decoded input: " . print_r($input, true));
    
    // Validate required fields
    if (empty($input['full-name']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required']);
        exit;
    }
    
    // Sanitize input
    $fullName = trim($input['full-name']);
    $email = trim($input['email']);
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $guests = isset($input['guests']) ? $input['guests'] : '1';
    $dietary = isset($input['dietary']) ? trim($input['dietary']) : '';
    
    // Additional validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    
    // Generate discount for this user
    $discount = generateDiscount();
    
    // Create RSVP record
    $rsvpRecord = [
        'id' => null, // Will be set when saving
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'guests' => $guests,
        'dietary' => $dietary,
        'discount' => $discount,
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Read current database
    $database = readDatabase();
    
    // Set ID
    $database['last_id']++;
    $rsvpRecord['id'] = $database['last_id'];
    
    // Add to database
    $database['rsvp_data'][] = $rsvpRecord;
    
    // Save database
    $writeResult = writeDatabase($database);
    if ($writeResult !== false) {
        // Return success response with user data
        echo json_encode([
            'success' => true,
            'message' => 'RSVP submitted successfully',
            'user_data' => [
                'name' => $fullName,
                'email' => $email,
                'discount' => $discount
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save RSVP data. Check file permissions.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

