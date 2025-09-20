# Laravel E-Commerce CMS Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Requirements](#system-requirements)
3. [Local Development Setup](#local-development-setup)
4. [Docker Setup](#docker-setup)
5. [CMS Dashboard Access](#cms-dashboard-access)
6. [Environment Configuration](#environment-configuration)
7. [API Documentation](#api-documentation)
8. [Database Schema](#database-schema)
9. [Troubleshooting](#troubleshooting)
10. [Additional Resources](#additional-resources)

---

## Project Overview

This is a Laravel-based E-Commerce Content Management System (CMS) built with modern web technologies. The application provides a comprehensive admin dashboard for managing products, categories, orders, users, and administrators.

### Key Features
- **Admin Dashboard**: Complete CMS interface for managing all aspects of the e-commerce platform
- **User Management**: Admin and user account management with role-based access
- **Product Management**: Full CRUD operations for products and categories
- **Order Management**: Order processing, status updates, and invoice generation
- **Shopping Cart**: API-based cart functionality with checkout process
- **Authentication**: Secure login system with Laravel Sanctum
- **Responsive Design**: Modern UI built with Bootstrap and Tailwind CSS

### Technology Stack
- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Bootstrap 5, Tailwind CSS, Alpine.js
- **Database**: MySQL 8.0 / SQLite
- **Authentication**: Laravel Sanctum
- **Containerization**: Docker & Docker Compose
- **Build Tools**: Vite, NPM

---

## System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: 8.x or higher
- **Database**: MySQL 8.0+ or SQLite
- **Web Server**: Apache/Nginx (or use Laravel's built-in server)

### Recommended Development Environment
- **Operating System**: Windows 10/11, macOS, or Linux
- **IDE**: VS Code, PhpStorm, or similar
- **Docker**: Latest version (for containerized development)
- **Git**: For version control

---

## Local Development Setup

### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd demo
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Node.js Dependencies
```bash
npm install
```

### Step 4: Environment Configuration
Create a `.env` file in the project root:

```env
APP_NAME="Laravel E-Commerce CMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Database Configuration (Choose one)
# For SQLite (Default)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# For MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=demo
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Cache Configuration
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Redis Configuration (Optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Step 5: Generate Application Key
```bash
php artisan key:generate
```

### Step 6: Database Setup

#### For SQLite (Recommended for Development)
```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
```

#### For MySQL
```bash
# Create database
mysql -u root -p
CREATE DATABASE demo;

# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
```

### Step 7: Create Storage Link
```bash
php artisan storage:link
```

### Step 8: Build Frontend Assets
```bash
# For development
npm run dev

# For production
npm run build
```

### Step 9: Start Development Server
```bash
# Start Laravel development server
php artisan serve

# Or use the development script (includes queue worker and logs)
composer run dev
```

The application will be available at `http://localhost:8000`

---

## Docker Setup

### Prerequisites
- Docker Desktop installed and running
- Docker Compose v2.0+

### Step 1: Environment Configuration for Docker
Create a `.env` file with Docker-specific settings:

```env
APP_NAME="Laravel E-Commerce CMS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:6162

# Database Configuration for Docker
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=demo
DB_USERNAME=demo
DB_PASSWORD=secret

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Cache Configuration
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Step 2: Build and Start Containers
```bash
# Build and start all services
docker-compose up -d --build

# View logs
docker-compose logs -f
```

### Step 3: Install Dependencies Inside Container
```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose exec node npm install

# Build frontend assets
docker-compose exec node npm run build
```

### Step 4: Application Setup
```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed

# Create storage link
docker-compose exec app php artisan storage:link
```

### Step 5: Access the Application
- **Web Application**: http://localhost:6162
- **Mailpit (Email Testing)**: http://localhost:8025
- **MySQL Database**: localhost:3306

### Docker Services
The Docker setup includes the following services:

1. **app**: PHP-FPM container with Laravel application
2. **web**: Nginx web server
3. **db**: MySQL 8.0 database
4. **mailpit**: Email testing tool
5. **node**: Node.js container for frontend builds

### Useful Docker Commands
```bash
# Stop all services
docker-compose down

# Stop and remove volumes
docker-compose down -v

# Rebuild specific service
docker-compose build app

# Execute commands in container
docker-compose exec app php artisan migrate
docker-compose exec node npm run dev

# View service logs
docker-compose logs app
docker-compose logs web
docker-compose logs db
```

---

## CMS Dashboard Access

### Default Admin Account
After running the database seeder, you can access the admin dashboard with:

- **URL**: `http://localhost:8000/admin/dashboard` (local) or `http://localhost:6162/admin/dashboard` (Docker)
- **Email**: `admin@example.com`
- **Password**: `password`

### Dashboard Features

#### 1. Main Dashboard (`/admin/dashboard`)
- Overview of system statistics
- Recent orders and activities
- Quick access to main features

#### 2. User Management (`/admin/users`)
- **View Users**: List all registered users
- **Add User**: Create new user accounts
- **Edit User**: Modify user information
- **Delete User**: Remove user accounts

#### 3. Admin Management (`/admin/admins`)
- **View Admins**: List all admin accounts
- **Add Admin**: Create new admin accounts
- **Edit Admin**: Modify admin information
- **Delete Admin**: Remove admin accounts

#### 4. Category Management (`/admin/categories`)
- **View Categories**: List all product categories
- **Add Category**: Create new categories
- **Edit Category**: Modify category information
- **Delete Category**: Remove categories

#### 5. Product Management (`/admin/products`)
- **View Products**: List all products with filtering options
- **Add Product**: Create new products with images and details
- **Edit Product**: Modify product information
- **Delete Product**: Remove products

#### 6. Order Management (`/admin/orders`)
- **View Orders**: List all orders with status filtering
- **Add Order**: Manually create orders
- **Edit Order**: Modify order details and status
- **Delete Order**: Remove orders
- **Generate Invoice**: Create PDF invoices for orders

### Navigation Structure
```
Dashboard
├── Dashboard (Overview)
├── User Management
│   ├── Users
│   └── Admins
├── Product Management
│   ├── Categories
│   └── Products
└── Order Management
    └── Orders
```

### Access Control
- All admin routes are protected by authentication middleware
- Admin-specific routes require admin role verification
- Regular users cannot access admin dashboard features

---

## Environment Configuration

### Required Environment Variables

#### Application Settings
```env
APP_NAME="Laravel E-Commerce CMS"    # Application name
APP_ENV=local                         # Environment (local, production, testing)
APP_KEY=                             # Application encryption key (auto-generated)
APP_DEBUG=true                       # Debug mode (false for production)
APP_URL=http://localhost:8000        # Application URL
```

#### Database Configuration
```env
# For SQLite (Development)
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# For MySQL (Production/Docker)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1                    # Database host
DB_PORT=3306                         # Database port
DB_DATABASE=demo                     # Database name
DB_USERNAME=root                     # Database username
DB_PASSWORD=                         # Database password
```

#### Mail Configuration
```env
MAIL_MAILER=smtp                     # Mail driver
MAIL_HOST=mailpit                    # SMTP host
MAIL_PORT=1025                       # SMTP port
MAIL_USERNAME=null                   # SMTP username
MAIL_PASSWORD=null                   # SMTP password
MAIL_ENCRYPTION=null                 # Encryption (tls, ssl, null)
MAIL_FROM_ADDRESS="hello@example.com" # From email address
MAIL_FROM_NAME="${APP_NAME}"         # From name
```

#### Cache and Session Configuration
```env
CACHE_DRIVER=file                    # Cache driver (file, redis, database)
FILESYSTEM_DISK=local                # File storage disk
QUEUE_CONNECTION=sync                # Queue driver (sync, database, redis)
SESSION_DRIVER=file                  # Session driver (file, database, redis)
SESSION_LIFETIME=120                 # Session lifetime in minutes
```

#### Redis Configuration (Optional)
```env
REDIS_HOST=127.0.0.1                # Redis host
REDIS_PASSWORD=null                  # Redis password
REDIS_PORT=6379                      # Redis port
```

### Production Environment Considerations

#### Security Settings
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong database credentials
DB_PASSWORD=your_secure_password

# Configure proper mail settings
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Use Redis for better performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Performance Optimization
```env
# Enable OPcache in production
OPCACHE_ENABLE=1

# Use Redis for caching
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login User
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Logout User
```http
POST /api/logout
Authorization: Bearer {token}
```

### Cart Endpoints

#### Get Cart
```http
GET /api/cart
Authorization: Bearer {token}
```

#### Add to Cart
```http
POST /api/cart/add-to-cart
Authorization: Bearer {token}
Content-Type: application/json

{
    "product_id": 1,
    "quantity": 2
}
```

#### Remove from Cart
```http
POST /api/cart/remove
Authorization: Bearer {token}
Content-Type: application/json

{
    "cart_item_id": 1
}
```

#### Update Quantity
```http
POST /api/cart/edit-quantity
Authorization: Bearer {token}
Content-Type: application/json

{
    "cart_item_id": 1,
    "quantity": 3
}
```

#### Empty Cart
```http
POST /api/cart/empty
Authorization: Bearer {token}
```

#### Checkout
```http
POST /api/cart/checkout
Authorization: Bearer {token}
Content-Type: application/json

{
    "shipping_address": "123 Main St, City, State 12345",
    "payment_method": "credit_card"
}
```

### Order Endpoints

#### Get Orders
```http
GET /api/orders
Authorization: Bearer {token}
```

#### Get Specific Order
```http
GET /api/orders/show?id=1
Authorization: Bearer {token}
```

#### Update Order Status
```http
POST /api/orders/change-status
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": 1,
    "status": "shipped"
}
```

#### Assign Order
```http
POST /api/orders/assign
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": 1,
    "admin_id": 2
}
```

---

## Database Schema

### Core Tables

#### Users Table
```sql
- id (bigint, primary key)
- name (varchar)
- email (varchar, unique)
- email_verified_at (timestamp)
- password (varchar)
- is_admin (boolean, default false)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Categories Table
```sql
- id (bigint, primary key)
- name (varchar)
- description (text)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Products Table
```sql
- id (bigint, primary key)
- name (varchar)
- description (text)
- price (decimal)
- category_id (bigint, foreign key)
- image (varchar)
- stock_quantity (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Orders Table
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- total_amount (decimal)
- status (varchar)
- shipping_address (text)
- payment_method (varchar)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Order Items Table
```sql
- id (bigint, primary key)
- order_id (bigint, foreign key)
- product_id (bigint, foreign key)
- quantity (integer)
- price (decimal)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Carts Table
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Cart Items Table
```sql
- id (bigint, primary key)
- cart_id (bigint, foreign key)
- product_id (bigint, foreign key)
- quantity (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

#### Order Logs Table
```sql
- id (bigint, primary key)
- order_id (bigint, foreign key)
- status (varchar)
- notes (text)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. Application Key Not Set
**Error**: `No application encryption key has been specified.`

**Solution**:
```bash
php artisan key:generate
```

#### 2. Database Connection Issues
**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solutions**:
- Check database credentials in `.env` file
- Ensure database server is running
- For Docker: Verify database container is running with `docker-compose ps`

#### 3. Permission Issues (Linux/macOS)
**Error**: `Permission denied` when accessing storage

**Solution**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 4. Composer Memory Limit
**Error**: `Fatal error: Allowed memory size exhausted`

**Solution**:
```bash
php -d memory_limit=-1 /usr/local/bin/composer install
```

#### 5. Node.js Build Issues
**Error**: `npm ERR! peer dep missing`

**Solution**:
```bash
rm -rf node_modules package-lock.json
npm install
```

#### 6. Docker Container Issues
**Error**: Container fails to start

**Solutions**:
```bash
# Check container logs
docker-compose logs app

# Rebuild containers
docker-compose down
docker-compose up -d --build

# Clear Docker cache
docker system prune -a
```

#### 7. Mail Configuration Issues
**Error**: Emails not sending

**Solution**:
- For development: Use Mailpit (included in Docker setup)
- Check mail configuration in `.env` file
- Verify SMTP credentials for production

### Performance Optimization

#### 1. Enable OPcache (Production)
Add to `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

#### 2. Use Redis for Caching
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### 3. Optimize Composer Autoloader
```bash
composer dump-autoload --optimize
```

#### 4. Clear Application Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Additional Resources

### Laravel Documentation
- [Laravel Official Documentation](https://laravel.com/docs)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel UI Documentation](https://laravel.com/docs/ui)

### Development Tools
- [Composer Documentation](https://getcomposer.org/doc/)
- [Docker Documentation](https://docs.docker.com/)
- [NPM Documentation](https://docs.npmjs.com/)

### Frontend Resources
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)

### Database Resources
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [SQLite Documentation](https://www.sqlite.org/docs.html)

### Support and Community
- [Laravel Community](https://laravel.com/community)
- [Laracasts](https://laracasts.com/)
- [Laravel News](https://laravel-news.com/)

---

## Contact Information

For technical support or questions about this project, please contact:

- **Project Repository**: [GitHub Repository URL]
- **Documentation**: [Documentation URL]
- **Issues**: [GitHub Issues URL]

---

*This documentation was generated for the Laravel E-Commerce CMS project. Please keep this document updated as the project evolves.*
