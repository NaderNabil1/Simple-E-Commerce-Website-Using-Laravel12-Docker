# 🐳 Laravel E-Commerce Docker Setup

Quick start guide for running the Laravel E-Commerce Management System with Docker.

## 🚀 Quick Start

### Prerequisites
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Docker Compose
- Git

### One-Command Setup

**Linux/Mac:**
```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

**Windows:**
```cmd
docker-setup.bat
```

### Manual Setup

1. **Clone and navigate to project:**
   ```bash
   git clone <repository-url>
   cd demo
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

3. **Start containers:**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies:**
   ```bash
   docker-compose exec app composer install
   docker-compose exec node npm install
   ```

5. **Setup application:**
   ```bash
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

6. **Build frontend:**
   ```bash
   docker-compose exec node npm run build
   ```

## 🌐 Access Points

- **Web Application**: http://localhost:6162
- **Admin Panel**: http://localhost:6162/admin
- **API**: http://localhost:6162/api
- **Email Testing**: http://localhost:8025

## 🔧 Common Commands

```bash
# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Access app container
docker-compose exec app bash

# Run Artisan commands
docker-compose exec app php artisan [command]

# Run Composer commands
docker-compose exec app composer [command]

# Run NPM commands
docker-compose exec node npm [command]
```

## 📚 Documentation

- **Complete Docker Guide**: [DOCKER_DOCUMENTATION.md](DOCKER_DOCUMENTATION.md)
- **API Documentation**: [Project_Documentation.md](Project_Documentation.md)

## 🛠️ Services

| Service | Port | Description |
|---------|------|-------------|
| **app** | 9000 | Laravel PHP-FPM |
| **web** | 6162 | Nginx Web Server |
| **db** | 3306 | MySQL Database |
| **mailpit** | 8025 | Email Testing |
| **node** | - | Node.js Frontend |

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Check what's using the port
netstat -tulpn | grep :6162

# Stop conflicting services
sudo systemctl stop apache2
```

### Permission Issues
```bash
# Fix permissions
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### Database Connection Issues
```bash
# Check database logs
docker-compose logs db

# Test connection
docker-compose exec app php artisan tinker
```

### Container Won't Start
```bash
# Check logs
docker-compose logs [service-name]

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## 🚀 Production Deployment

1. **Use production compose file:**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

2. **Update environment variables:**
   ```bash
   # Set production values in .env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

3. **Optimize Laravel:**
   ```bash
   docker-compose exec app php artisan config:cache
   docker-compose exec app php artisan route:cache
   docker-compose exec app php artisan view:cache
   ```

## 📋 Environment Variables

Key environment variables for Docker setup:

```env
# Application
APP_NAME="Laravel E-Commerce"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:6162

# Database (Docker MySQL)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=demo
DB_USERNAME=demo
DB_PASSWORD=secret

# Mail (Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:6162,127.0.0.1:6162
```

## 🔒 Security Notes

- Change default passwords in production
- Use HTTPS in production
- Keep Docker images updated
- Use secrets management for sensitive data
- Implement proper firewall rules

## 📞 Support

For issues and questions:
1. Check the troubleshooting section
2. Review the complete Docker documentation
3. Check Docker and Laravel logs
4. Contact the development team

---

**Happy coding! 🚀**

