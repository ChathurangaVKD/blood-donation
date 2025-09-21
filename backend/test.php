<?php
// Simple test script to verify backend routing works
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Backend routing is working!',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

