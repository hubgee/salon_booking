# Wink & Glow - FREE Deployment Plan for Client Demo

## Goal
Deploy the salon booking website on **completely free** platforms to demonstrate to your client, including working push notifications.

---

## Recommended Free Stack

Since your app requires both PHP+MySQL AND Node.js, we need to use a combination of free services:

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FREE DEPLOYMENT ARCHITECTURE                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│   ┌─────────────────────────────────────────────────────────────┐   │
│   │                    InfinityFree.com                          │   │
│   │              FREE PHP Hosting + MySQL                        │   │
│   │                                                              │   │
│   │   • Free subdomain: yoursite.infinityfreeapp.com            │   │
│   │   • Free SSL certificate                                     │   │
│   │   • 5GB storage                                              │   │
│   │   • MySQL database included                                  │   │
│   │   • Unlimited bandwidth                                      │   │
│   │                                                              │   │
│   │   Hosts: index.html, booking.html, dashboard.php,           │   │
│   │          all PHP files, CSS, JS, images                     │   │
│   └─────────────────────────────────────────────────────────────┘   │
│                              │                                       │
│                              │ API calls                             │
│                              ▼                                       │
│   ┌─────────────────────────────────────────────────────────────┐   │
│   │                      Render.com                              │   │
│   │              FREE Node.js Web Service                        │   │
│   │                                                              │   │
│   │   • Free subdomain: yourapp.onrender.com                    │   │
│   │   • Free SSL certificate                                     │   │
│   │   • 750 hours/month free                                     │   │
│   │   • Spins down after 15 min inactivity                      │   │
│   │                                                              │   │
│   │   Hosts: realtime-server for push notifications             │   │
│   └─────────────────────────────────────────────────────────────┘   │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Alternative: Railway.app Free Tier

Railway offers $5 free credit per month which should be enough for a demo:

```
┌─────────────────────────────────────────────────────────────────────┐
│                      Railway.app - $5 Free Credit                    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│   ┌───────────────┐  ┌───────────────┐  ┌───────────────────────┐   │
│   │ PHP Service   │  │ Node.js       │  │ MySQL Database        │   │
│   │ Main Website  │  │ Push Server   │  │ salon_booking         │   │
│   └───────────────┘  └───────────────┘  └───────────────────────┘   │
│                                                                      │
│   Single platform - easier to manage                                 │
│   Free subdomain with SSL                                            │
│   $5 credit = ~500 hours of runtime                                  │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## RECOMMENDED: Railway.app (Simplest Free Option)

For a client demo, Railway is the easiest because everything is in one place.

### Step-by-Step Deployment Guide

#### Phase 1: Prepare Your Code

##### 1.1 Create GitHub Repository

If you don't have one already:
```bash
cd c:\xampp\htdocs\salon_booking
git init
git add .
git commit -m "Initial commit"
```

Then create a repo on GitHub and push:
```bash
git remote add origin https://github.com/YOUR_USERNAME/salon-booking.git
git push -u origin main
```

##### 1.2 Files to Modify

**[`db.php`](../db.php)** - Update for environment variables:
```php
<?php
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'salon_booking';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    die('DB connect error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
```

##### 1.3 Create Configuration Files

**Create `Procfile`** in root directory:
```
web: php -S 0.0.0.0:$PORT -t .
```

**Create `nixpacks.toml`** in root directory:
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.mysqli"]

[start]
cmd = "php -S 0.0.0.0:${PORT:-8080} -t ."
```

**Create `.gitignore`** in root directory:
```
# Environment
.env
vapid-keys.json

# Dependencies
node_modules/

# IDE
.vscode/
.idea/

# OS
.DS_Store
Thumbs.db

# Local config
*.local.php
```

##### 1.4 Update Realtime Server for Production

**Create `realtime-server/Procfile`**:
```
web: node server.js
```

#### Phase 2: Deploy to Railway

##### 2.1 Create Railway Account
1. Go to https://railway.app
2. Click "Login" → "Login with GitHub"
3. Authorize Railway

##### 2.2 Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Select your salon-booking repository
4. Railway will auto-detect PHP

##### 2.3 Add MySQL Database
1. In your project dashboard, click "+ New"
2. Select "Database" → "Add MySQL"
3. Railway creates the database automatically
4. Click on MySQL service to see connection details

##### 2.4 Set Environment Variables

Click on your PHP service → "Variables" tab → Add these:

| Variable | Value |
|----------|-------|
| `MYSQLHOST` | (auto-linked from MySQL) |
| `MYSQLUSER` | (auto-linked from MySQL) |
| `MYSQLPASSWORD` | (auto-linked from MySQL) |
| `MYSQLDATABASE` | (auto-linked from MySQL) |
| `MYSQLPORT` | (auto-linked from MySQL) |

##### 2.5 Deploy Node.js Service

1. Click "+ New" → "GitHub Repo"
2. Select same repo but set root directory to `realtime-server`
3. Add environment variables:

| Variable | Value |
|----------|-------|
| `DB_HOST` | (copy from MySQL) |
| `DB_USER` | (copy from MySQL) |
| `DB_PASS` | (copy from MySQL) |
| `DB_NAME` | (copy from MySQL) |
| `DB_PORT` | (copy from MySQL) |
| `NOTIFY_TOKEN` | `demo-token-12345` |
| `VAPID_PUBLIC_KEY` | (generate new - see below) |
| `VAPID_PRIVATE_KEY` | (generate new - see below) |

##### 2.6 Generate VAPID Keys

Run locally:
```bash
cd realtime-server
npm install
node generate-vapid-keys.js
```

Copy the keys from `vapid-keys.json` to Railway environment variables.

#### Phase 3: Database Setup

##### 3.1 Connect to Railway MySQL

Use Railway CLI or a MySQL client:
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Connect to MySQL
railway connect mysql
```

##### 3.2 Create Tables

Run these SQL commands:

```sql
-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2),
    duration INT
);

-- Insert sample services
INSERT INTO services (service_name, price, duration) VALUES
('Classic Lashes', 5000, 60),
('Volume Lashes', 8000, 90),
('Hybrid Lashes', 6500, 75);

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    service INT,
    date DATE,
    time TIME,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service) REFERENCES services(id)
);

-- Create admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert default admin (password: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'booking_created',
    is_seen TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seen_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- Create push subscriptions table
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint TEXT NOT NULL,
    p256dh TEXT NOT NULL,
    auth TEXT NOT NULL,
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_endpoint (endpoint(255))
);
```

#### Phase 4: Update Frontend for Production

##### 4.1 Update Socket.io Connection

In [`js/script.js`](../js/script.js), find the Socket.io connection and update:

```javascript
// Change from localhost to your Railway Node.js URL
const REALTIME_SERVER = 'https://your-nodejs-service.up.railway.app';
const socket = io(REALTIME_SERVER);
```

##### 4.2 Update VAPID Public Key

In [`dashboard.php`](../dashboard.php) or wherever the push subscription is created, update the VAPID public key to match your generated key.

#### Phase 5: Test Your Deployment

##### 5.1 Access Your Site
- PHP App: `https://your-php-service.up.railway.app`
- Node.js: `https://your-nodejs-service.up.railway.app`

##### 5.2 Test Checklist
- [ ] Homepage loads correctly
- [ ] Booking form submits successfully
- [ ] Admin login works (admin / admin123)
- [ ] Dashboard shows appointments
- [ ] Push notifications work when new booking is made

---

## Quick Reference: Railway URLs

After deployment, your URLs will look like:
- **Main Site:** `https://salon-booking-production.up.railway.app`
- **Realtime Server:** `https://realtime-server-production.up.railway.app`

---

## Troubleshooting

### Database Connection Failed
- Check environment variables are set correctly
- Verify MySQL service is running in Railway

### Push Notifications Not Working
- Ensure HTTPS is being used (Railway provides this)
- Check VAPID keys are correctly set
- Verify Node.js service is running
- Check browser console for errors

### Site Shows Errors
- Check Railway logs: Click service → "Logs" tab
- Verify all PHP files uploaded correctly

---

## Demo Tips for Client Presentation

1. **Test everything beforehand** - Make a test booking, check notifications
2. **Have backup screenshots** - In case of network issues
3. **Show mobile view** - The PWA install feature
4. **Demonstrate the admin dashboard** - Show real-time updates
5. **Keep Railway dashboard open** - To show it's a real deployment

---

## Next Steps After Demo

Once your client approves:
1. Purchase a custom domain
2. Upgrade to paid hosting for reliability
3. Set up proper backups
4. Implement additional security measures

---

## Need Help?

Let me know if you want me to:
1. Prepare all the code changes needed for deployment
2. Create a complete SQL setup script
3. Help troubleshoot any deployment issues
