<?php
// index.php - Enhanced server router for BloodLink PHP Built-in Server
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Debug logging for troubleshooting
error_log("BloodLink Router: Processing request for path: " . $path);

// Handle backend API requests
if (strpos($path, '/backend/') === 0) {
    $filename = basename($path);
    $file = __DIR__ . '/backend/' . $filename;

    error_log("BloodLink Router: Backend request for file: " . $file);

    if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        // Set working directory to backend for includes to work
        chdir(__DIR__ . '/backend');
        include $file;
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Backend file not found: ' . $filename]);
        exit;
    }
}

// Handle frontend static files (CSS, JS, images)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    $file = __DIR__ . '/frontend' . $path;
    if (file_exists($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'css':
                header('Content-Type: text/css; charset=utf-8');
                break;
            case 'js':
                header('Content-Type: application/javascript; charset=utf-8');
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
            case 'ico':
                header('Content-Type: image/x-icon');
                break;
            case 'woff':
                header('Content-Type: font/woff');
                break;
            case 'woff2':
                header('Content-Type: font/woff2');
                break;
            case 'ttf':
                header('Content-Type: font/ttf');
                break;
            case 'eot':
                header('Content-Type: application/vnd.ms-fontobject');
                break;
        }

        // Enable caching for static files
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
}

// Handle frontend HTML files
if ($path === '/' || $path === '/index.html') {
    header('Content-Type: text/html; charset=utf-8');
    readfile(__DIR__ . '/frontend/index.html');
    exit;
}

// Handle specific frontend pages with proper routing
$frontendPages = [
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
        header('Content-Type: text/html; charset=utf-8');
        readfile($file);
        exit;
    }
}

// Try to find any other file in frontend directory
$frontendFile = __DIR__ . '/frontend' . $path;
if (file_exists($frontendFile)) {
    if (pathinfo($frontendFile, PATHINFO_EXTENSION) === 'html') {
        header('Content-Type: text/html; charset=utf-8');
    }
    readfile($frontendFile);
    exit;
}

// Default fallback to index.html for SPA behavior
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__ . '/frontend/index.html');
?>
