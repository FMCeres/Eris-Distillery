<?php
// Test script for RSVP submission
header('Content-Type: application/json');

// Test data
$testData = [
    'full-name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '555-1234',
    'guests' => '2',
    'dietary' => 'Vegetarian'
];

echo "Testing RSVP submission with data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Simulate the submission
$jsonData = json_encode($testData);
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://localhost/submit_rsvp.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response (HTTP $httpCode):\n";
echo $response . "\n\n";

// Check database
if (file_exists('database.json')) {
    echo "Database contents:\n";
    echo file_get_contents('database.json');
} else {
    echo "Database file not found.\n";
}
?>



