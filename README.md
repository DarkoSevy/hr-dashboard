# HR Dashboard

A full-stack HR management system built with React (Vite) and PHP.

## 🚀 Quick Start

### Prerequisites

- Docker and Docker Compose
- Node.js 18+ (for local development)
- PHP 8.1+ (for local development)

### Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd hr-dashboard
```

2. Copy environment files:
```bash
cp frontend/.env.example frontend/.env
cp backend/.env.example backend/.env
```

3. Start the application:
```bash
docker-compose up --build -d
```

## 🌐 Access Points

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- Database: localhost:3306
- Redis: localhost:6379

## 📝 API Endpoints

### Employees
- GET    /api/employees     - List all employees
- POST   /api/employees     - Create new employee
- GET    /api/employees/:id - Get specific employee
- PUT    /api/employees/:id - Update employee
- DELETE /api/employees/:id - Delete employee

### Tasks
- GET    /api/tasks        - List all tasks
- POST   /api/tasks        - Create new task
- GET    /api/tasks/:id    - Get specific task
- PUT    /api/tasks/:id    - Update task
- DELETE /api/tasks/:id    - Delete task

## 🛠️ Development

### Frontend Development

The frontend uses Vite with the following features:
- Proxy configuration for API requests
- Environment variable support
- Hot Module Replacement (HMR)

Example API call:
```javascript
// Using the proxy configuration
const response = await fetch('/api/employees');
const data = await response.json();
```

### Backend Development

The backend API includes:
- CORS configuration for development
- Environment variable support
- Database migrations
- Redis caching

## 🔧 Configuration

### Frontend (.env)
```env
VITE_NODE_ENV=development
VITE_API_URL=/api
VITE_PORT=3000
```

### Backend (.env)
```env
APP_ENV=development
FRONTEND_URL=http://localhost:3000
DB_HOST=mysql
DB_NAME=hr_dashboard
DB_USER=root
DB_PASS=root
```

## 📦 Docker Services

- frontend: React application (port 3000)
- backend: PHP API (port 8000)
- mysql: Database (port 3306)
- redis: Caching (port 6379)

## 🔒 Security

- CORS is configured to allow only specific origins
- API requests are validated
- Database credentials are managed via environment variables
- Redis is used for caching and rate limiting

## 🐛 Troubleshooting

1. CORS Issues:
   - Check FRONTEND_URL in backend/.env
   - Verify Vite proxy configuration
   - Check allowed origins in backend/api/index.php

2. Database Connection:
   - Ensure MySQL container is running
   - Verify database credentials in .env
   - Check network connectivity between containers

3. API Errors:
   - Check backend logs: `docker-compose logs backend`
   - Verify API endpoints and request format
   - Check PHP error logs

## 📚 Additional Resources

- [Vite Documentation](https://vitejs.dev/)
- [React Documentation](https://reactjs.org/)
- [PHP Documentation](https://www.php.net/)
- [Docker Documentation](https://docs.docker.com/) 