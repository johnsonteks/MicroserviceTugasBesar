#!/bin/bash

set -e  # Stop the script if any command fails

echo "🛑 Stopping all services..."

# Stop queue worker in product service first
echo "🔄 Stopping queue worker in Product Service..."
docker exec product-service-app pkill -f "php artisan queue:work" 2>/dev/null || echo "   Queue worker not running or already stopped"

# Stop all containers
echo "📦 Stopping all Docker containers..."
docker-compose down

# Optional: Remove volumes (uncomment if you want to delete all data)
# echo "🗑️  Removing volumes..."
# docker-compose down -v

# Optional: Remove network (uncomment if you want to remove the network)
# echo "🔧 Removing Docker network 'laravel-net'..."
# docker network rm laravel-net 2>/dev/null || echo "   Network already removed or doesn't exist"

echo "✅ All services have been stopped successfully."
echo ""
echo "💡 Tips:"
echo "   - To start again: ./setup.sh"
echo "   - To remove volumes: docker-compose down -v"
echo "   - To remove network: docker network rm laravel-net"