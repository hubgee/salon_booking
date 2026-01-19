# PWA + Push Notifications Setup Guide

## Prerequisites

1. **Database Setup**: Run the SQL scripts to create required tables:
   - `create_notifications_table.sql` (if not already run)
   - `create_push_subscriptions_table.sql`

2. **Node.js Dependencies**: Install required packages:
   ```bash
   cd realtime-server
   npm install
   ```

3. **VAPID Keys**: Generate VAPID keys for push notifications:
   ```bash
   cd realtime-server
   node generate-vapid-keys.js
   ```
   This will create `vapid-keys.json` with your public and private keys.

4. **App Icons**: Create the required icon files:
   - `images/icons/icon-192x192.png` (192x192 pixels)
   - `images/icons/icon-512x512.png` (512x512 pixels)
   
   You can use an online tool like https://realfavicongenerator.net/ or resize your existing logo.

## HTTPS Requirement

**Important**: Web Push API and Service Workers require HTTPS in production (localhost works for development).

- **Development**: Works on `http://localhost` without SSL
- **Production**: You need an SSL certificate (Let's Encrypt, Cloudflare, etc.)

## Setup Steps

### 1. Database Tables
Run these SQL scripts in your MySQL client:
```sql
-- Run create_notifications_table.sql
-- Run create_push_subscriptions_table.sql
```

### 2. Install Node.js Dependencies
```bash
cd realtime-server
npm install
```

### 3. Generate VAPID Keys
```bash
cd realtime-server
node generate-vapid-keys.js
```

This creates `vapid-keys.json`. Keep the private key secure!

### 4. Start Node.js Server
```bash
cd realtime-server
npm start
```

The server should start on port 3001.

### 5. Configure Environment Variables (Optional)
You can set these in `realtime-server/server.js` or use environment variables:
- `DB_HOST` (default: localhost)
- `DB_USER` (default: root)
- `DB_PASS` (default: empty)
- `DB_NAME` (default: salon_booking)
- `NOTIFY_TOKEN` (default: change-this-token)
- `VAPID_PUBLIC_KEY` (from vapid-keys.json)
- `VAPID_PRIVATE_KEY` (from vapid-keys.json)

### 6. Create App Icons
Place your app icons in:
- `images/icons/icon-192x192.png`
- `images/icons/icon-512x512.png`

## Testing

1. **Open Admin Dashboard**: Navigate to `dashboard.php` and log in
2. **Install PWA**: Look for the "Install App" button in the navbar
3. **Grant Permissions**: Allow notification permissions when prompted
4. **Test Push**: Create a booking from the homepage - you should receive a push notification
5. **Check Badge**: The app icon should show a badge counter with unread bookings

## Browser Support

- **Chrome/Edge (Desktop + Android)**: Full support (push + badge)
- **Firefox**: Push supported, badge limited
- **Safari (iOS 16.4+)**: Push supported, badge not supported
- **Fallback**: Navbar badge always works regardless of browser

## Troubleshooting

### Push Notifications Not Working
- Check browser console for errors
- Verify VAPID keys are generated and loaded
- Ensure Node.js server is running
- Check that notification permissions are granted
- Verify HTTPS is enabled (required for production)

### Badge Not Showing
- Badge API is only supported on Chrome/Edge
- Check browser console for errors
- Verify service worker is registered
- Check that unread count is being fetched correctly

### Service Worker Not Registering
- Check browser console for errors
- Verify `service-worker.js` is accessible
- Ensure HTTPS is enabled (or using localhost)
- Clear browser cache and try again

## Security Notes

- Keep VAPID private key secure (server-side only)
- Change `NOTIFY_TOKEN` in production
- Use HTTPS in production
- Validate all push subscription endpoints

