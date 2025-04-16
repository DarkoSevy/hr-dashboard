@echo off
echo Installing frontend dependencies...
call npm install

echo Installing backend dependencies...
cd backend
call composer install
cd ..

echo Creating .env file if it doesn't exist...
if not exist backend\.env (
    copy backend\.env.example backend\.env
    echo Please update the .env file with your configuration.
)

echo Creating required directories...
mkdir src\assets src\hooks src\utils src\types src\styles
mkdir src\components\common src\components\dashboard src\components\employees
mkdir src\components\layout src\components\reports src\components\tasks
mkdir src\services\api
mkdir backend\api\v1 backend\middleware backend\services backend\utils backend\tests

echo Setup complete! You can now start the development server with:
echo 1. For Docker: docker-compose up
echo 2. For local development:
echo    - Frontend: npm run dev
echo    - Backend: Start your local server (e.g., XAMPP) 