<?php
// router.php - Fixed router to handle both frontend and backend requests properly

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Log the request for debugging
error_log("Router: $method $uri");

// Handle backend API requests - FIXED VERSION
if (strpos($uri, '/backend/') === 0) {
    // Remove the leading slash and construct the file path
    $backendPath = ltrim($uri, '/');
    $backendFile = __DIR__ . '/' . $backendPath;

    error_log("Backend request detected. Looking for file: $backendFile");

    if (file_exists($backendFile) && is_file($backendFile)) {
        // Set proper headers before including the file
        error_log("Backend file found, executing: $backendFile");
        require $backendFile;
        return true;
    } else {
        error_log("Backend file not found: $backendFile");
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Backend endpoint not found', 'path' => $backendFile]);
        return true;
    }
}

// Handle frontend requests
if ($uri === '/' || $uri === '/index.html' || $uri === '') {
    require __DIR__ . '/frontend/index.html';
    return true;
}

// Handle other frontend files with proper MIME types
$frontendFile = __DIR__ . '/frontend' . $uri;
if (file_exists($frontendFile) && is_file($frontendFile)) {
    // Set proper MIME type based on file extension
    $extension = pathinfo($frontendFile, PATHINFO_EXTENSION);
    switch ($extension) {
        case 'css':
            header('Content-Type: text/css');
            break;
        case 'js':
            header('Content-Type: application/javascript');
            break;
        case 'html':
            header('Content-Type: text/html');
            break;
        case 'json':
            header('Content-Type: application/json');
            break;
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        case 'svg':
            header('Content-Type: image/svg+xml');
            break;
        default:
            header('Content-Type: text/plain');
            break;
    }

    readfile($frontendFile);
    return true;
}

// If no file found, return 404
http_response_code(404);
echo "404 - File not found: $uri";
return false;
?>
