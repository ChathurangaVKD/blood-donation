<?php
// index.php - Working router that properly executes backend PHP scripts
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$query = parse_url($request, PHP_URL_QUERY);

// Handle backend API requests - FIXED IMPLEMENTATION
if (strpos($path, '/backend/') === 0) {
    $filename = basename($path);
    $backendFile = __DIR__ . '/backend/' . $filename;

    if (file_exists($backendFile) && pathinfo($backendFile, PATHINFO_EXTENSION) === 'php') {
        // Set up environment for backend script
        if ($query) {
            $_SERVER['QUERY_STRING'] = $query;
            parse_str($query, $_GET);
        }

        // Execute the backend PHP script directly
        require $backendFile;
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Backend endpoint not found']);
        exit;
    }
}

// Handle frontend static files
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $path)) {
    $file = __DIR__ . '/frontend' . $path;
    if (file_exists($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'css': header('Content-Type: text/css'); break;
            case 'js': header('Content-Type: application/javascript'); break;
            case 'png': header('Content-Type: image/png'); break;
            case 'jpg':
            case 'jpeg': header('Content-Type: image/jpeg'); break;
            case 'gif': header('Content-Type: image/gif'); break;
            case 'svg': header('Content-Type: image/svg+xml'); break;
            case 'ico': header('Content-Type: image/x-icon'); break;
        }

        readfile($file);
        exit;
    }
}

// Handle frontend HTML pages
$frontendPages = [
    '/' => 'index.html',
    '/index.html' => 'index.html',
    '/login.html' => 'login.html',
    '/register.html' => 'register.html',
    '/monitor.html' => 'monitor.html',
    '/request.html' => 'request.html',
    '/search.html' => 'search.html',
    '/contact.html' => 'contact.html',
    '/admin.html' => 'admin.html'
];

if (isset($frontendPages[$path])) {
    $file = __DIR__ . '/frontend/' . $frontendPages[$path];
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
        exit;
    }
}

// Default fallback to index.html
header('Content-Type: text/html');
readfile(__DIR__ . '/frontend/index.html');
?>
