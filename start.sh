#!/bin/bash
# start.sh - Simple startup script for Blood Donation System using PHP Built-in Server

echo "🩸 Blood Donation System - PHP Built-in Server"
echo "=============================================="
echo

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ ERROR: PHP is not installed or not in PATH"
    echo "Please install PHP and make sure it's accessible from command line"
    exit 1
fi

echo "✅ PHP found: $(php --version | head -n1)"

# Setup database with comprehensive sample data
echo "🚀 Setting up database with comprehensive sample data..."
cd database
php reset_and_populate.php
if [ $? -ne 0 ]; then
    echo "❌ Database setup failed. Please check MySQL connection."
    exit 1
fi
cd ..

echo
echo "🔧 Starting backend server on http://localhost:8081..."
cd backend
php -S localhost:8081 &
BACKEND_PID=$!
cd ..

echo "🌐 Starting frontend server on http://localhost:8080..."
cd frontend
php -S localhost:8080 &
FRONTEND_PID=$!
cd ..

echo "⏳ Waiting for servers to start..."
sleep 3

echo "🚀 Opening frontend in browser..."
if command -v open &> /dev/null; then
    # macOS
    open "http://localhost:8080"
elif command -v xdg-open &> /dev/null; then
    # Linux
    xdg-open "http://localhost:8080"
else
    echo "Please open http://localhost:8080 in your browser"
fi

echo
echo "✅ Blood Donation System is running!"
echo "   📱 Frontend: http://localhost:8080"
echo "   🔧 Backend API: http://localhost:8081"
echo "   📊 Database: Populated with 29 donors, 47 inventory units"
echo
echo "🔐 Admin Login: admin / admin123"
echo
echo "📝 Both frontend and backend now run on PHP Built-in Servers"
echo
echo "Press Ctrl+C to stop all servers..."

# Wait for user to press Ctrl+C
trap "echo; echo 'Stopping servers...'; kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; echo 'All servers stopped. Goodbye!'; exit 0" INT

while true; do
    sleep 1
done
