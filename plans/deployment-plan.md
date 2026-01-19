# Wink & Glow Salon Booking - Deployment Plan

## Project Overview

**Application:** Wink & Glow Lash Salon Booking System  
**Tech Stack:**
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
- **Backend:** PHP 7.4+ with MySQL database
- **Realtime Server:** Node.js with Express, Socket.io, and Web Push
- **Features:** PWA support, Push Notifications, Admin Dashboard

## Requirements Summary

| Requirement | Value |
|-------------|-------|
| Budget | Under $10/month |
| Push Notifications | Essential |
| Domain | Free subdomain initially |
| Technical Level | Advanced |

---

## Recommended Hosting Options

Given your requirements, here are the best options ranked by recommendation:

### Option 1: Railway.app (RECOMMENDED)

**Cost:** Free tier available, then ~$5/month  
**Why:** Supports both PHP and Node.js, free subdomain with SSL, easy deployment

```
┌─────────────────────────────────────────────────────────────┐
│                      Railway.app                             │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ PHP Service │  │ Node.js     │  │ MySQL Database      │  │
│  │ Main App    │  │ Realtime    │  │ salon_booking       │  │
│  │ Port 80     │  │ Port 3001   │  │                     │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
│                                                              │
│  Free subdomain: yourapp.up.railway.app                     │
│  Free SSL included                                           │
└─────────────────────────────────────────────────────────────┘
```

**Pros:**
- ✅ Supports PHP + Node.js + MySQL in one platform
- ✅ Free tier with $5 credit/month
- ✅ Automatic SSL certificates
- ✅ Free subdomain provided
- ✅ Easy GitHub integration for CI/CD
- ✅ Environment variables management

**Cons:**
- ⚠️ Free tier has usage limits
- ⚠️ May need to upgrade for production traffic

---

### Option 2: Render.com + PlanetScale

**Cost:** Free tier available  
**Why:** Generous free tier, good for PHP and Node.js

```
┌────────────────────────────────────────────────────────────────┐
│                         Architecture                            │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────┐      ┌─────────────────┐                  │
│  │   Render.com    │      │   Render.com    │                  │
│  │   PHP Service   │      │   Node.js       │                  │
│  │   Docker        │      │   Web Service   │                  │
│  └────────┬────────┘      └────────┬────────┘                  │
│           │                        │                            │
│           └────────────┬───────────┘                            │
│                        │                                        │
│                        ▼                                        │
│           ┌─────────────────────────┐                          │
│           │     PlanetScale         │                          │
│           │     MySQL Database      │                          │
│           │     Free Tier           │                          │
│           └─────────────────────────┘                          │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

**Pros:**
- ✅ Generous free tier
- ✅ Auto-scaling
- ✅ Free SSL
- ✅ PlanetScale offers serverless MySQL

**Cons:**
- ⚠️ PHP requires Docker setup
- ⚠️ More complex configuration
- ⚠️ Services may sleep on free tier

---

### Option 3: DigitalOcean Droplet

**Cost:** $4-6/month  
**Why:** Full control, VPS with everything you need

```
┌─────────────────────────────────────────────────────────────┐
│              DigitalOcean Droplet - $4/month                │
│                    Ubuntu 22.04 LTS                         │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                    Nginx                              │   │
│  │              Reverse Proxy + SSL                      │   │
│  └──────────────────────────────────────────────────────┘   │
│                          │                                   │
│           ┌──────────────┴──────────────┐                   │
│           │                             │                    │
│           ▼                             ▼                    │
│  ┌─────────────────┐         ┌─────────────────┐            │
│  │   PHP-FPM       │         │   Node.js       │            │
│  │   Port 9000     │         │   PM2           │            │
│  │                 │         │   Port 3001     │            │
│  └─────────────────┘         └─────────────────┘            │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                    MySQL 8.0                          │   │
│  │                 salon_booking DB                      │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Pros:**
- ✅ Full server control
- ✅ Predictable pricing
- ✅ Can host multiple sites
- ✅ No cold starts or sleeping

**Cons:**
- ⚠️ Requires server management
- ⚠️ Need to set up SSL manually with Let's Encrypt
- ⚠️ Need to manage security updates

---

### Option 4: Vercel + Railway Hybrid

**Cost:** Free  
**Why:** Best free option if you restructure slightly

- **Vercel:** Host static files and PHP via serverless functions
- **Railway:** Host Node.js realtime server and MySQL

---

## Recommended Approach: Railway.app

Based on your requirements, I recommend **Railway.app** because:

1. **Single Platform:** Manages PHP, Node.js, and MySQL together
2. **Budget-Friendly:** Free tier to start, affordable scaling
3. **Push Notifications:** Full Node.js support for your realtime server
4. **SSL Included:** Required for push notifications
5. **Easy Deployment:** Git-based deployment

---

## Deployment Steps for Railway.app

### Phase 1: Prepare Codebase

#### Step 1.1: Create Production Configuration Files

Create a `railway.json` for PHP service:
```json
{
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t .",
    "healthcheckPath": "/",
    "healthcheckTimeout": 100
  }
}
```

#### Step 1.2: Update Database Configuration

Modify [`db.php`](db.php) to use environment variables:
```php
<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'salon_booking';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('DB connect error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
```

#### Step 1.3: Update Node.js Server Configuration

The [`realtime-server/server.js`](realtime-server/server.js) already uses environment variables, which is good.

#### Step 1.4: Create .gitignore

```
# Environment files
.env
vapid-keys.json

# Dependencies
node_modules/

# IDE
.vscode/
.idea/

# OS files
.DS_Store
Thumbs.db
```

### Phase 2: Set Up Railway

#### Step 2.1: Create Railway Account
1. Go to https://railway.app
2. Sign up with GitHub

#### Step 2.2: Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Connect your repository

#### Step 2.3: Add MySQL Database
1. In your project, click "New"
2. Select "Database" → "MySQL"
3. Railway will provision a MySQL instance

#### Step 2.4: Configure Environment Variables

Set these variables in Railway dashboard:

**For PHP Service:**
```
DB_HOST=<from MySQL service>
DB_USER=<from MySQL service>
DB_PASS=<from MySQL service>
DB_NAME=railway
REALTIME_SERVER_URL=<Node.js service URL>
```

**For Node.js Service:**
```
DB_HOST=<from MySQL service>
DB_USER=<from MySQL service>
DB_PASS=<from MySQL service>
DB_NAME=railway
NOTIFY_TOKEN=<generate-secure-token>
VAPID_PUBLIC_KEY=<your-public-key>
VAPID_PRIVATE_KEY=<your-private-key>
```

### Phase 3: Database Migration

#### Step 3.1: Connect to Railway MySQL
Use Railway CLI or MySQL client to connect

#### Step 3.2: Run SQL Scripts
Execute in order:
1. Create `appointments` table (if not exists)
2. Create `services` table (if not exists)
3. Run `create_notifications_table.sql`
4. Run `create_push_subscriptions_table.sql`

### Phase 4: Deploy

#### Step 4.1: Push to GitHub
```bash
git add .
git commit -m "Prepare for production deployment"
git push origin main
```

#### Step 4.2: Verify Deployment
1. Check Railway dashboard for build status
2. Access your app via the provided subdomain
3. Test booking flow
4. Test admin dashboard
5. Test push notifications

---

## Code Changes Required

### 1. Update [`db.php`](db.php) for Environment Variables

```php
<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'salon_booking';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die('DB connect error: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
```

### 2. Update Realtime Server URL References

In [`js/script.js`](js/script.js), update Socket.io connection to use environment-based URL or relative path.

### 3. Add Nixpacks Configuration

Create `nixpacks.toml` for PHP:
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.mysqli", "php82Extensions.pdo_mysql"]

[start]
cmd = "php -S 0.0.0.0:${PORT:-8080} -t ."
```

---

## Security Checklist Before Deployment

- [ ] Change default admin credentials in `login.php`
- [ ] Generate new VAPID keys for production
- [ ] Set strong `NOTIFY_TOKEN`
- [ ] Remove any hardcoded credentials
- [ ] Enable HTTPS only
- [ ] Set secure session cookies
- [ ] Add CSRF protection
- [ ] Validate all user inputs

---

## Post-Deployment Tasks

1. **Monitor:** Set up uptime monitoring (UptimeRobot - free)
2. **Backups:** Configure database backups
3. **Domain:** When ready, add custom domain in Railway
4. **Analytics:** Consider adding Google Analytics

---

## Alternative: Quick Start with InfinityFree + Glitch

If Railway doesn't work out, here's a free alternative:

1. **InfinityFree** (free PHP hosting with MySQL)
   - Host PHP files
   - Free subdomain
   - Free SSL

2. **Glitch.com** (free Node.js hosting)
   - Host realtime server
   - Free subdomain

This requires splitting your app across two platforms but is completely free.

---

## Questions?

Let me know if you'd like me to:
1. Proceed with preparing the codebase for Railway deployment
2. Explore a different hosting option in more detail
3. Help with any specific configuration

