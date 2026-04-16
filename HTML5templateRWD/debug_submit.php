<?php
// Debug version of submit_rsvp.php
header('Content-Type: text/plain');

echo "=== RSVP Debug Information ===\n\n";

echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "\n";
echo "Raw POST data:\n";
echo file_get_contents('php://input') . "\n\n";

echo "JSON decode test:\n";
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "Decoded data:\n";
    print_r($input);
}

echo "\nFile permissions:\n";
echo "Current directory: " . getcwd() . "\n";
echo "Database file exists: " . (file_exists('database.json') ? 'Yes' : 'No') . "\n";
if (file_exists('database.json')) {
    echo "Database file readable: " . (is_readable('database.json') ? 'Yes' : 'No') . "\n";
    echo "Database file writable: " . (is_writable('database.json') ? 'Yes' : 'No') . "\n";
}
echo "Current directory writable: " . (is_writable('.') ? 'Yes' : 'No') . "\n";

echo "\nPHP Error Log (last 5 lines):\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    echo implode('', array_slice($lines, -5));
} else {
    echo "Error log not available or empty.\n";
}
?>



