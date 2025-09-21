#!/bin/bash

# start.sh - Blood Donation System Startup Script
echo "Starting Blood Donation System..."

# Kill any existing PHP server instances
echo "Stopping any existing servers..."
pkill -f "php -S localhost:8080" 2>/dev/null || true

# Wait a moment for processes to terminate
sleep 2

# Clear any cached session files and logs
echo "Cleaning up old sessions and logs..."
rm -f /tmp/sess_* 2>/dev/null || true
rm -f server.log server_output.log 2>/dev/null || true

# Start the PHP development server with proper routing
echo "Starting PHP server on http://localhost:8080..."
php -S localhost:8080 router.php > server.log 2>&1 &

# Get the process ID
SERVER_PID=$!

# Wait a moment for server to start
sleep 3

# Check if server started successfully
if ps -p $SERVER_PID > /dev/null; then
    echo "✅ Server started successfully!"
    echo "🌐 Open http://localhost:8080 in your browser"
    echo "📋 Monitor page: http://localhost:8080/monitor.html"
    echo "🔐 Login page: http://localhost:8080/login.html"
    echo ""
    echo "📝 Server logs are being written to server.log"
    echo "🛑 To stop the server, run: pkill -f 'php -S localhost:8080'"
    echo ""
    echo "Press Ctrl+C to stop monitoring logs (server will continue running)"
    echo "Server logs:"
    echo "----------------------------------------"

    # Follow the log file
    tail -f server.log
else
    echo "❌ Failed to start server!"
    exit 1
fi
