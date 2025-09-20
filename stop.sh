#!/bin/bash

# Blood Donation System - Stop Script

echo "🛑 Stopping Blood Donation System..."

# Stop and remove containers
docker-compose down

# Optional: Remove volumes (uncomment if you want to reset database)
# docker-compose down -v

echo "✅ All services stopped successfully!"
echo ""
echo "🔄 To start again: ./start.sh"
echo "🗑️  To reset database: docker-compose down -v && ./start.sh"
