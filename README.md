# Users Manager

A simple user management API built with **PHP 8.2**, **Slim Framework 4**, **PostgreSQL** and **Docker**. The entire environment (PHP, Composer and PostgreSQL) runs inside Docker containers.

## 📦 Requirements
- Docker
- Docker Compose

## 🚀  Features
- REST API using Slim 4
- PostgreSQL running in Docker
- Automatic user table creation using `database/setup.php`
- Composer dependencies installed inside the container
- JWT authentication for protected routes
- Swagger UI documentation available inside the project.

## 🚀 Project Structure
```
/project
  ├─ public/
  │   ├─ index.php
  │   ├─ docs/ # OpenAPI specification (openapi.yaml)
  │   └─ swagger/ # Swagger UI assets
  ├─ database/
  │   └─ setup.php # Creates users table in the DB container
  ├─ src/
  │   ├─ Controllers/
  │   ├─ Http/
  │   ├─ Middlewares/
  │   ├─ Models/
  │   ├─ Repositories/
  │   ├─ Services/
  │   ├─ Database.php
  │   └─ routes.php
  ├─ composer.json
  ├─ docker-compose.yaml
  └─ Dockerfile
```

## 🔧 Environment Variables
Create your `.env` file based on `.env.example` before running the containers. This file stores important setting such as:
- Database credentials
- JWT secret key

Example variables:
```
DB_HOST=db
DB_PORT=5432
DB_NAME=usersmanager
DB_USER=postgres
DB_PASS=postgres
SECRET_KEY=your-secret-key
```

## 🐳 Running the Project
#### 1. Build and start the containers
```bash
docker compose up --build -d
```

#### 2. Install Composer dependencies
```bash
docker compose exec php bash -lc "composer install"
```

#### 3. Initialize the database
```bash
docker compose exec php bash -lc "php database/setup.php"
```

## 📡 API Documentation
This project includes **Swagger UI**.

#### 📄 How to access the documentation
After the containers are running, open:
```
http://localhost:8002/docs
```
Inside Swagger UI you'll find all available endpoints, request/response bodies, auth requirements and examples.