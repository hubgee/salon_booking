// server.js
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const webpush = require('web-push');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*" } // allow all origins for now
});

app.use(cors());
app.use(express.json());

// Simple auth token to protect notify endpoint
const AUTH_TOKEN = process.env.NOTIFY_TOKEN || 'change-this-token';

// Database configuration (match db.php settings)
const DB_CONFIG = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASS || '',
  database: process.env.DB_NAME || 'salon_booking'
};

// Load VAPID keys
let VAPID_PUBLIC_KEY = '';
let VAPID_PRIVATE_KEY = '';

try {
  // Try to load from file first
  const keysPath = path.join(__dirname, 'vapid-keys.json');
  if (fs.existsSync(keysPath)) {
    const keys = JSON.parse(fs.readFileSync(keysPath, 'utf8'));
    VAPID_PUBLIC_KEY = keys.publicKey;
    VAPID_PRIVATE_KEY = keys.privateKey;
  } else {
    // Try environment variables
    VAPID_PUBLIC_KEY = process.env.VAPID_PUBLIC_KEY || '';
    VAPID_PRIVATE_KEY = process.env.VAPID_PRIVATE_KEY || '';
  }
  
  if (VAPID_PUBLIC_KEY && VAPID_PRIVATE_KEY) {
    webpush.setVapidDetails(
      'mailto:admin@salonbooking.com', // Contact email
      VAPID_PUBLIC_KEY,
      VAPID_PRIVATE_KEY
    );
    console.log('VAPID keys loaded successfully');
  } else {
    console.warn('WARNING: VAPID keys not found. Run node generate-vapid-keys.js to generate them.');
  }
} catch (error) {
  console.error('Error loading VAPID keys:', error);
}

// Get VAPID public key endpoint (for frontend subscription)
app.get('/vapid-public-key', (req, res) => {
  if (!VAPID_PUBLIC_KEY) {
    return res.status(500).json({ error: 'VAPID keys not configured' });
  }
  res.json({ publicKey: VAPID_PUBLIC_KEY });
});

// Socket.IO connection
io.on('connection', (socket) => {
  console.log('A user connected');

  // Clients join rooms (admin or client)
  socket.on('join', ({ role, email }) => {
    if (role === 'admin') socket.join('admin');
    if (role === 'client' && email) socket.join(`client:${email}`);
  });

  socket.on('disconnect', () => {
    console.log('A user disconnected');
  });
});

// Endpoint PHP can call to trigger notifications
app.post('/notify', (req, res) => {
  const { token, event, data, target } = req.body;
  if (token !== AUTH_TOKEN) return res.status(401).json({ error: 'Unauthorized' });

  if (target === 'admin') {
    io.to('admin').emit(event, data);
  } else if (target && target.startsWith('client:')) {
    io.to(target).emit(event, data);
  } else {
    io.emit(event, data); // broadcast to all
  }

  res.json({ ok: true });
});

// Endpoint to send push notifications
app.post('/send-push', async (req, res) => {
  const { token, bookingData, unreadCount } = req.body;
  
  if (token !== AUTH_TOKEN) {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  if (!VAPID_PUBLIC_KEY || !VAPID_PRIVATE_KEY) {
    return res.status(500).json({ error: 'VAPID keys not configured' });
  }

  try {
    // Get all push subscriptions from database
    const connection = await mysql.createConnection(DB_CONFIG);
    const [subscriptions] = await connection.execute(
      'SELECT endpoint, p256dh, auth FROM push_subscriptions'
    );
    await connection.end();

    if (subscriptions.length === 0) {
      return res.json({ ok: true, sent: 0, message: 'No push subscriptions found' });
    }

    // Prepare push notification payload
    const serviceName = bookingData.service_name || `Service ID ${bookingData.service}`;
    const payload = JSON.stringify({
      title: 'New Booking',
      body: `${bookingData.name} booked ${serviceName} on ${bookingData.date} at ${bookingData.time}`,
      icon: 'images/icons/icon-192x192.png',
      badge: 'images/icons/icon-192x192.png',
      data: {
        url: 'dashboard.php',
        unreadCount: unreadCount || 1
      }
    });

    // Send push notification to all subscriptions
    const pushPromises = subscriptions.map(async (sub) => {
      try {
        const subscription = {
          endpoint: sub.endpoint,
          keys: {
            p256dh: sub.p256dh,
            auth: sub.auth
          }
        };
        await webpush.sendNotification(subscription, payload);
        return { success: true, endpoint: sub.endpoint };
      } catch (error) {
        console.error('Push notification failed:', error);
        // If subscription is invalid, remove it from database
        if (error.statusCode === 410 || error.statusCode === 404) {
          try {
            const connection = await mysql.createConnection(DB_CONFIG);
            await connection.execute('DELETE FROM push_subscriptions WHERE endpoint = ?', [sub.endpoint]);
            await connection.end();
          } catch (dbError) {
            console.error('Failed to remove invalid subscription:', dbError);
          }
        }
        return { success: false, endpoint: sub.endpoint, error: error.message };
      }
    });

    const results = await Promise.allSettled(pushPromises);
    const successful = results.filter(r => r.status === 'fulfilled' && r.value.success).length;
    
    res.json({ 
      ok: true, 
      sent: successful, 
      total: subscriptions.length,
      results: results.map(r => r.status === 'fulfilled' ? r.value : { success: false, error: r.reason })
    });
  } catch (error) {
    console.error('Error sending push notifications:', error);
    res.status(500).json({ error: 'Failed to send push notifications', details: error.message });
  }
});

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => console.log(`Realtime server running on port ${PORT}`));

app.get('/', (req, res) => {
    res.send('Server is alive!');
  });
  
