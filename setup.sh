#!/bin/bash

# Blood Donation System - Complete Setup Guide
# This script will help you get the system running locally with Docker

echo "🩸 Blood Donation System - Complete Docker Setup"
echo "================================================"
echo ""

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo "❌ Docker is not running. Please start Docker Desktop and try again."
        echo ""
        echo "To start Docker Desktop:"
        echo "1. Open Applications folder"
        echo "2. Double-click on Docker.app"
        echo "3. Wait for Docker to start (you'll see a whale icon in your menu bar)"
        echo "4. Run this script again: ./setup.sh"
        exit 1
    fi
    echo "✅ Docker is running"
}

# Check Docker installation
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker Desktop first."
    echo "Visit: https://docs.docker.com/desktop/install/mac/"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Desktop first."
    echo "Visit: https://docs.docker.com/desktop/install/mac/"
    exit 1
fi

echo "✅ Docker and Docker Compose are installed"

# Check if Docker is running
check_docker

# Stop any existing containers
echo "🛑 Stopping any existing containers..."
docker-compose down > /dev/null 2>&1

# Build and start services
echo "🏗️  Building and starting services..."
echo "This may take a few minutes on first run..."
if docker-compose up -d --build; then
    echo "✅ Services started successfully!"
else
    echo "❌ Failed to start services. Check Docker Desktop and try again."
    exit 1
fi

# Wait for MySQL to be ready
echo "⏳ Waiting for database to be ready..."
sleep 15

# Check if services are running
echo "🔍 Checking service status..."
if docker-compose ps | grep -q "Up"; then
    echo ""
    echo "🎉 Blood Donation System is now running!"
    echo ""
    echo "🌐 Access URLs:"
    echo "   📱 Web Application: http://localhost:8080"
    echo "   🗄️  phpMyAdmin: http://localhost:8081"
    echo ""
    echo "🔐 Database Connection:"
    echo "   Host: localhost"
    echo "   Port: 3306"
    echo "   Database: blood_donation"
    echo "   Username: blooddonation"
    echo "   Password: blooddonation123"
    echo ""
    echo "👤 Demo Login Credentials:"
    echo "   Email: john.doe@email.com"
    echo "   Password: password123"
    echo ""
    echo "🔧 Management Commands:"
    echo "   View logs: docker-compose logs -f"
    echo "   Stop system: docker-compose down"
    echo "   Restart: docker-compose restart"
    echo ""
    echo "📚 For more info, see README.md"
else
    echo "❌ Some services failed to start. Checking logs..."
    docker-compose logs
    exit 1
fi
