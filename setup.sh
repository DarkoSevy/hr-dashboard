#!/bin/bash

# Install frontend dependencies
echo "Installing frontend dependencies..."
npm install

# Install backend dependencies
echo "Installing backend dependencies..."
cd backend
composer install

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "Please update the .env file with your configuration."
fi

# Create required directories
echo "Creating required directories..."
mkdir -p src/{assets,hooks,utils,types,styles} src/components/{common,dashboard,employees,layout,reports,tasks} src/services/api
mkdir -p backend/{api/v1,middleware,services,utils,tests}

# Set permissions
echo "Setting permissions..."
chmod -R 755 .
chmod -R 777 backend/storage

echo "Setup complete! You can now start the development server with:"
echo "1. For Docker: docker-compose up"
echo "2. For local development:"
echo "   - Frontend: npm run dev"
echo "   - Backend: Start your local server (e.g., XAMPP)" 