# Quick Deployment Guide for Railway.app

Your codebase is now ready for deployment! Follow these steps:

## Step 1: Push Changes to GitHub

```bash
cd c:\xampp\htdocs\salon_booking
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

## Step 2: Create Railway Account & Project

1. Go to https://railway.app
2. Click **"Login"** → **"Login with GitHub"**
3. Click **"New Project"** → **"Deploy from GitHub repo"**
4. Select your `salon_booking` repository
5. Railway will auto-detect PHP and start building

## Step 3: Add MySQL Database

1. In your Railway project, click **"+ New"**
2. Select **"Database"** → **"Add MySQL"**
3. Wait for it to provision (takes ~30 seconds)

## Step 4: Link Database Variables

1. Click on your **PHP service**
2. Go to **"Variables"** tab
3. Click **"Add Variable Reference"**
4. Add these from your MySQL service:
   - `MYSQLHOST`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`
   - `MYSQLPORT`

## Step 5: Deploy Node.js Realtime Server

1. Click **"+ New"** → **"GitHub Repo"**
2. Select the same repository
3. In settings, set **Root Directory** to: `realtime-server`
4. Add the same MySQL variables plus:
   - `NOTIFY_TOKEN` = `your-secure-token-here`
   - `VAPID_PUBLIC_KEY` = (from vapid-keys.json)
   - `VAPID_PRIVATE_KEY` = (from vapid-keys.json)

## Step 6: Connect PHP to Node.js

1. Get your Node.js service URL (e.g., `https://realtime-server-xxx.up.railway.app`)
2. In your PHP service variables, add:
   - `REALTIME_SERVER_URL` = `https://realtime-server-xxx.up.railway.app`

## Step 7: Set Up Database Tables

1. In Railway, click on MySQL service
2. Click **"Connect"** → **"Query"**
3. Copy and paste the contents of `database/setup.sql`
4. Run the query

## Step 8: Test Your Deployment

1. Get your PHP service URL (e.g., `https://salon-booking-xxx.up.railway.app`)
2. Open it in your browser
3. Test:
   - [ ] Homepage loads
   - [ ] Booking form works
   - [ ] Admin login works (admin / admin123)
   - [ ] Push notifications work

## Environment Variables Summary

### PHP Service
| Variable | Value |
|----------|-------|
| MYSQLHOST | (from MySQL) |
| MYSQLUSER | (from MySQL) |
| MYSQLPASSWORD | (from MySQL) |
| MYSQLDATABASE | (from MySQL) |
| MYSQLPORT | (from MySQL) |
| REALTIME_SERVER_URL | https://your-nodejs-service.up.railway.app |

### Node.js Service
| Variable | Value |
|----------|-------|
| DB_HOST | (from MySQL) |
| DB_USER | (from MySQL) |
| DB_PASS | (from MySQL) |
| DB_NAME | (from MySQL) |
| DB_PORT | (from MySQL) |
| NOTIFY_TOKEN | your-secure-token |
| VAPID_PUBLIC_KEY | (from vapid-keys.json) |
| VAPID_PRIVATE_KEY | (from vapid-keys.json) |

## Troubleshooting

### Build Failed
- Check Railway logs for errors
- Ensure all files are committed to GitHub

### Database Connection Error
- Verify MySQL variables are linked correctly
- Check that database tables are created

### Push Notifications Not Working
- Ensure VAPID keys are set correctly
- Verify Node.js service is running
- Check browser console for errors

## Files Modified for Deployment

- `db.php` - Uses environment variables
- `config.php` - Centralized configuration (NEW)
- `dashboard.php` - Dynamic realtime server URL
- `process_booking.php` - Uses config for URLs
- `confirm_booking.php` - Uses config for URLs
- `Procfile` - PHP start command (NEW)
- `nixpacks.toml` - Railway PHP config (NEW)
- `.gitignore` - Excludes sensitive files (NEW)
- `database/setup.sql` - Complete DB setup (NEW)
- `realtime-server/Procfile` - Node.js start command (NEW)
