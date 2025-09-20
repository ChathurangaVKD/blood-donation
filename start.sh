#!/bin/bash

# Blood Donation System - Local Development Setup Script

echo "🩸 Blood Donation System - Docker Setup"
echo "========================================"

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    echo "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    echo "Visit: https://docs.docker.com/compose/install/"
    exit 1
fi

echo "✅ Docker and Docker Compose are installed"

# Stop any existing containers
echo "🛑 Stopping any existing containers..."
docker-compose down

# Build and start services
echo "🏗️  Building and starting services..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 30

# Check if services are running
echo "🔍 Checking service status..."
if docker-compose ps | grep -q "Up"; then
    echo "✅ Services are running successfully!"
    echo ""
    echo "🌐 Application URLs:"
    echo "   - Web Application: http://localhost:8080"
    echo "   - phpMyAdmin: http://localhost:8081"
    echo ""
    echo "🗄️  Database Connection:"
    echo "   - Host: localhost"
    echo "   - Port: 3306"
    echo "   - Database: blood_donation"
    echo "   - Username: blooddonation"
    echo "   - Password: blooddonation123"
    echo ""
    echo "👤 Default Login Credentials:"
    echo "   - Email: john.doe@email.com"
    echo "   - Password: password123"
    echo ""
    echo "🔧 Admin Panel:"
    echo "   - Username: admin"
    echo "   - Password: admin123"
    echo ""
    echo "📝 To view logs: docker-compose logs -f"
    echo "🛑 To stop: docker-compose down"
else
    echo "❌ Some services failed to start. Check logs with: docker-compose logs"
    exit 1
fi
