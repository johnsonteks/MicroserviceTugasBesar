#!/bin/bash

set -e  # Stop the script if any command fails

# Create Docker network if it doesn't exist
echo "🔧 Creating Docker network 'laravel-net' if not exists..."
docker network inspect laravel-net >/dev/null 2>&1 || \
docker network create laravel-net

echo "🚀 Starting all services..."
docker-compose up -d

# echo "✅ Running migrations and seeding for User Service..."
# docker exec -it user-service-app php artisan migrate:refresh --seed

# echo "✅ Running migrations and seeding for Product Service..."
# docker exec -it product-service-app php artisan migrate:refresh --seed

echo "🚀 Starting queue worker in Product Service..."
docker exec -d product-service-app php artisan queue:work

# echo "✅ Running migrations and seeding for Order Service..."
# docker exec -it order-service-app php artisan migrate:refresh --seed

echo "🎉 All services are up and running!"
echo ""
echo "📋 Service URLs:"
echo "   - User Service UI PHPMyAdmin: http://localhost:8080/ SQL -> localhost:3307"
echo "   - Product Service UI PHPMyadmin: http://localhost:8081/ SQL -> localhost:3308" 
echo "   - Order Service UI PHPMyAdmin: http://localhost:8082/ SQL -> localhost:3309"
echo "   - RabbitMQ Management: http://localhost:15672 (guest/guest)"
echo "   - RabbitMQ AMQP: localhost:5672"
echo ""
echo "✅ All services have been started and configured successfully."