// service-worker.js
// Service Worker for PWA push notifications and badge management

const CACHE_NAME = 'salon-admin-v1';
// Only cache static assets. Dynamic pages like dashboard.php should be fetched from network.
const urlsToCache = [
  'css/style.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching files');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('Service Worker: Cache failed', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Push event - handle push notifications
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push notification received');
  
  let notificationData = {
    title: 'New Booking',
    body: 'You have a new booking',
    icon: 'images/icons/icon-192x192.png',
    badge: 'images/icons/icon-192x192.png',
    data: {
      url: 'dashboard.php',
      unreadCount: 1
    }
  };

  if (event.data) {
    try {
      const payload = event.data.json();
      notificationData = {
        title: payload.title || 'New Booking',
        body: payload.body || 'You have a new booking',
        icon: payload.icon || 'images/icons/icon-192x192.png',
        badge: payload.badge || 'images/icons/icon-192x192.png',
        data: payload.data || notificationData.data
      };
    } catch (e) {
      console.error('Service Worker: Error parsing push data', e);
    }
  }

  // Update app badge if supported
  const unreadCount = notificationData.data?.unreadCount || 0;
  if ('setAppBadge' in self.registration) {
    self.registration.setAppBadge(unreadCount).catch((error) => {
      console.error('Service Worker: Failed to set badge', error);
    });
  }

  // Show notification
  event.waitUntil(
    self.registration.showNotification(notificationData.title, {
      body: notificationData.body,
      icon: notificationData.icon,
      badge: notificationData.badge,
      data: notificationData.data,
      tag: 'booking-notification',
      requireInteraction: false
    })
  );
});

// Notification click event - open dashboard
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notification clicked');
  event.notification.close();

  const urlToOpen = event.notification.data?.url || 'dashboard.php';

  event.waitUntil(
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    }).then((clientList) => {
      // Check if dashboard is already open
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      // Open new window if not already open
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

// Message event - handle messages from main thread
self.addEventListener('message', (event) => {
  console.log('Service Worker: Message received', event.data);
  
  if (event.data && event.data.type === 'UPDATE_BADGE') {
    const count = event.data.count || 0;
    if ('setAppBadge' in self.registration) {
      if (count > 0) {
        self.registration.setAppBadge(count).catch((error) => {
          console.error('Service Worker: Failed to set badge', error);
        });
      } else {
        self.registration.clearAppBadge().catch((error) => {
          console.error('Service Worker: Failed to clear badge', error);
        });
      }
    }
  }
});

// Fetch event - network-first for navigations, cache-first for static assets
self.addEventListener('fetch', (event) => {
  const request = event.request;

  // For top-level navigation requests (HTML pages)
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => {
        // Fallback to cached dashboard if network fails (optional)
        return caches.match('dashboard.php');
      })
    );
    return;
  }

  // For static assets: try cache first, then network
  event.respondWith(
    caches.match(request).then((response) => {
      return response || fetch(request);
    })
  );
});

