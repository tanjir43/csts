# Customer Support Ticketing System

A full-stack customer support ticketing system built with Laravel 12, Vue.js 3, and real-time chat functionality using Pusher.

## 🚀 Features

### Core Functionality
- **User Authentication**: Registration, login, logout with Laravel Sanctum
- **Role-based Access Control**: Admin and Customer roles with Spatie Laravel Permission
- **Ticket Management**: Full CRUD operations with status tracking
- **Real-time Chat**: Live messaging between customers and admins
- **File Attachments**: Support for ticket attachments using Spatie Media Library
- **Comment System**: Threaded discussions on tickets

### Admin Features
- View all tickets from all users
- Update ticket status (Open, In Progress, Resolved, Closed)
- Update ticket priority (Low, Medium, High)
- Manage customer tickets
- Real-time chat with customers

### Customer Features
- Create and manage personal tickets
- View ticket history
- Comment on tickets
- Real-time chat with support staff
- Upload attachments

## 🛠 Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 with Composition API
- **Styling**: Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum (API tokens)
- **Authorization**: Spatie Laravel Permission
- **Media Handling**: Spatie Laravel Media Library
- **Real-time**: Pusher for WebSocket connections
- **Build Tool**: Vite

## 📋 Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- NPM
- MySQL 8.0+
- Pusher account (for real-time features)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/tanjir43/csts.git
cd csts
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup
Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csts
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Pusher Configuration
Add your Pusher credentials to `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 7. Run Migrations and Seeders
```bash
php artisan migrate:fresh --seed
```

### 8. Create Storage Link
```bash
php artisan storage:link
```

### 9. Build Frontend Assets
```bash
npm run build
```

## 🚀 Running the Application

### Development Mode
```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev

# Terminal 3: Run queue worker (for real-time features)
php artisan queue:work
```


## 👥 Default Users

After seeding, you can use these accounts:

**Admin Account:**
- Email: `admin@admin.com`
- Password: `12345678`

**Customer Account:**
- Email: `customer@customer.com`
- Password: `12345678`
