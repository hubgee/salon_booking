<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Fetch all appointments with service names
$sql = "SELECT a.id, a.name, s.service_name, a.date, a.time 
        FROM appointments a 
        JOIN services s ON a.service = s.id 
        ORDER BY a.date, a.time";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Admin Dashboard</title>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#212529">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Salon Admin">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root {
      --primary-color: #b76e79;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --safe-area-top: max(12px, env(safe-area-inset-top));
      --safe-area-bottom: max(12px, env(safe-area-inset-bottom));
    }

    * {
      -webkit-touch-callout: none;
      -webkit-user-select: none;
      user-select: none;
    }

    input, textarea, select, button {
      -webkit-user-select: text;
      user-select: text;
    }

    html, body {
      height: 100%;
      width: 100%;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    body {
      background-color: #f8f9fa;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', sans-serif;
      padding-top: var(--safe-area-top);
      padding-bottom: var(--safe-area-bottom);
    }

    /* Navbar Mobile Optimization */
    .navbar {
      margin-bottom: 0;
      padding: var(--safe-area-top) 1rem 1rem 1rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      background: linear-gradient(135deg, #b76e79 0%, #a65c67 100%);
    }

    .navbar-brand {
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0;
    }

    .navbar-text {
      font-size: 0.9rem;
      white-space: nowrap;
    }

    #notification-badge {
      font-size: 0.65rem;
      min-width: 1.75rem;
      height: 1.75rem;
      padding: 0.35rem 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: badgePulse 2s infinite;
    }

    @keyframes badgePulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    .navbar-text {
      position: relative;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    /* Main Container */
    .container-fluid {
      padding: 1rem var(--safe-area-bottom);
      max-width: 100%;
    }

    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      gap: 1rem;
    }

    .dashboard-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 600;
      color: #333;
    }

    .refresh-btn {
      padding: 0.5rem 1rem;
      font-size: 0.85rem;
      border-radius: 20px;
    }

    /* Appointments Container */
    #appointments-container {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    /* Card-based Appointment View (Mobile Optimized) */
    .appointment-card {
      display: flexbox;
      justify-content: space-between;
      align-items: center;
      flex-grow: 1;
      background: white;
      border-radius: 12px;
      padding: 1rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border-left: 4px solid var(--primary-color);
      transition: all 0.2s ease;
      animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .appointment-card.new-booking {
      background: #fff3cd;
      border-left-color: #ffc107;
      box-shadow: 0 2px 12px rgba(255, 193, 7, 0.3);
      animation: slideInRight 0.3s ease, highlightFade 3s ease-out 3s forwards;
    }

    @keyframes highlightFade {
      from { background: #fff3cd; }
      to { background: white; }
    }

    .appointment-card:active {
      background: #f5f5f5;
      transform: translateY(2px);
    }

    .appointment-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 0.75rem;
      gap: 0.75rem;
    }

    .appointment-name {
      font-size: 1.05rem;
      font-weight: 600;
      color: #333;
      margin: 0;
      flex: 1;
      word-break: break-word;
    }

    .appointment-status {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.35rem 0.65rem;
      border-radius: 20px;
      white-space: nowrap;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-pending {
      background: #fff3cd;
      color: #856404;
    }

    .status-confirmed {
      background: #d4edda;
      color: #155724;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
    }

    .appointment-details {
      display: flex;
      justify-content:space-between;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
      font-size: 0.9rem;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .detail-label {
      font-size: 0.8rem;
      color: #999;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .detail-value {
      font-size: 0.95rem;
      color: #333;
      font-weight: 500;
      word-break: break-word;
    }

    .appointment-service {
      grid-column: 1 / -1;
    }

    .appointment-service .detail-value {
      color: var(--primary-color);
      font-weight: 600;
    }

    /* Empty State */
    .empty-state {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
      text-align: center;
      color: #999;
    }

    .empty-state-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .empty-state p {
      margin: 0;
      font-size: 0.95rem;
    }

    /* Loading State */
    .loading-spinner {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
      background: rgba(0, 0, 0, 0.8);
      padding: 2rem;
      border-radius: 12px;
      color: white;
      text-align: center;
    }

    .loading-spinner.show {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
    }

    /* Pull-to-Refresh Indicator */
    .pull-to-refresh {
      text-align: center;
      padding: 1rem;
      color: #999;
      font-size: 0.85rem;
      display: none;
    }

    .pull-to-refresh.show {
      display: block;
    }

    /* Mobile Button Optimization */
    .btn {
      min-height: 44px;
      min-width: 44px;
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
      border-radius: 8px;
      transition: all 0.2s ease;
      font-weight: 500;
    }

    .btn:active {
      transform: scale(0.95);
    }

    .btn-outline-light:active {
      background-color: rgba(255, 255, 255, 0.2);
    }

    /* Auto-refresh Indicator */
    .auto-refresh-indicator {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      background: var(--primary-color);
      color: white;
      padding: 0.75rem 1rem;
      border-radius: 20px;
      font-size: 0.8rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      opacity: 0;
      transition: opacity 0.3s ease;
      pointer-events: none;
      z-index: 100;
    }

    .auto-refresh-indicator.show {
      opacity: 1;
    }

    /* Responsive Tables (Fallback) */
    .table {
      font-size: 0.85rem;
    }

    .table thead {
      position: sticky;
      top: 0;
      background: #212529;
      z-index: 10;
    }

    .table td {
      padding: 0.75rem 0.5rem;
      vertical-align: middle;
      word-break: break-word;
    }

    /* Landscape Mode */
    @media (orientation: landscape) {
      .navbar {
        padding: 0.5rem 1rem;
      }

      .navbar-brand {
        font-size: 1rem;
      }

      .dashboard-header h2 {
        font-size: 1.25rem;
      }

      .appointment-card {
        padding: 0.75rem;
      }

      .appointment-details {
        grid-template-columns: 1fr 1fr 1fr;
      }
    }

    /* Small Screens (< 360px) */
    @media (max-width: 359px) {
      .appointment-name {
        font-size: 0.95rem;
      }

      .appointment-details {
        font-size: 0.85rem;
        gap: 0.5rem;
      }

      .detail-value {
        font-size: 0.9rem;
      }

      .navbar-brand {
        font-size: 1rem;
      }
    }

    /* Large Screens Fallback to Table */
    @media (min-width: 768px) {
      #appointments-container.card-view .table {
        display: table;
      }

      /* Keep appointment cards visible on all screen sizes */
      #appointments-container.card-view .appointment-card {
        display: flex; /* Changed from 'none' to 'flex' */
      }

      #appointments-container.table-view .appointment-card {
        display: flex;
      }

      #appointments-container.table-view .table {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <span class="navbar-brand">Salon Admin</span>
    <div class="d-flex align-items-center gap-2">
      <span class="navbar-text">
        <span>🔔</span>
        <span id="notification-badge" class="badge bg-danger" style="display: none;">0</span>
      </span>
      <button id="install-button" class="btn btn-outline-light btn-sm" style="display: none;">Install</button>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<!-- Auto Refresh Indicator -->
<div class="auto-refresh-indicator" id="auto-refresh-indicator">
  ✓ Updated
</div>

<!-- Pull to Refresh Hint -->
<div class="pull-to-refresh" id="pull-to-refresh">
  ↓ Pull to refresh
</div>

<!-- Main Container -->
<div class="container-fluid">
  <div class="dashboard-header">
    <h2>Appointments</h2>
  </div>
  
  <!-- Loading Spinner -->
  <div class="loading-spinner" id="loading-spinner">
    <div class="spinner-border text-light" role="status"></div>
    <small>Loading appointments...</small>
  </div>

  <!-- Appointments Container - Card View for Mobile -->
  <div id="appointments-container" class="card-view">
    <!-- Appointments will be populated here -->
  </div>

  <!-- Fallback Table for larger screens -->
  <table class="table table-striped table-hover" style="display: none;">
    <thead class="table-dark">
      <tr>
        <th>Name</th>
        <th>Service</th>
        <th>Date</th>
        <th>Time</th>
      </tr>
    </thead>
    <tbody id="appointments-tbody">
      <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['service_name']) ?></td>
        <td><?= htmlspecialchars($row['date']) ?></td>
        <td><?= htmlspecialchars($row['time']) ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
  // ========== Socket.IO Connection ==========
  const socket = io('http://localhost:3001', { 
    transports: ['websocket'],
    reconnection: true,
    reconnectionDelay: 1000,
    reconnectionDelayMax: 5000,
    reconnectionAttempts: 5
  });
  
  socket.emit('join', { role: 'admin' });

  // ========== DOM Elements ==========
  const notificationBadge = document.getElementById('notification-badge');
  const installButton = document.getElementById('install-button');
  const loadingSpinner = document.getElementById('loading-spinner');
  const appointmentsContainer = document.getElementById('appointments-container');
  const autoRefreshIndicator = document.getElementById('auto-refresh-indicator');
  const pullToRefreshHint = document.getElementById('pull-to-refresh');
  
  let deferredPrompt = null;
  let vapidPublicKey = null;
  let lastRefreshTime = 0;

  // ========== PWA Installation ==========
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    installButton.style.display = 'block';
  });

  installButton.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    
    if (outcome === 'accepted') {
      console.log('PWA installed');
      installButton.style.display = 'none';
    }
    deferredPrompt = null;
  });

  if (window.matchMedia('(display-mode: standalone)').matches) {
    installButton.style.display = 'none';
  }

  // ========== Service Worker Registration ==========
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js')
      .then((registration) => {
        console.log('Service Worker registered');
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              console.log('New service worker available');
            }
          });
        });
      })
      .catch((error) => {
        console.error('Service Worker registration failed:', error);
      });
  }

  // ========== Badge Management ==========
  function updateNavbarBadge(count) {
    if (count > 0) {
      notificationBadge.textContent = count;
      notificationBadge.style.display = 'inline-flex';
    } else {
      notificationBadge.style.display = 'none';
    }
  }

  async function updateAppBadge(count) {
    if ('setAppBadge' in navigator) {
      try {
        if (count > 0) {
          await navigator.setAppBadge(count);
        } else {
          await navigator.clearAppBadge();
        }
      } catch (error) {
        console.error('Failed to update app badge:', error);
      }
    }
    
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
      navigator.serviceWorker.controller.postMessage({
        type: 'UPDATE_BADGE',
        count: count
      });
    }
  }

  function updateBadge(count) {
    updateNavbarBadge(count);
    updateAppBadge(count);
  }

  // ========== Appointments Table Refresh (Card View) ==========
  async function refreshAppointments() {
    try {
      loadingSpinner.classList.add('show');
      console.log('Refreshing appointments...');
      
      const response = await fetch('get_appointments.php');
      console.log('Response status:', response.status);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('Data received:', data);
      
      if (!data.success) {
        throw new Error(data.error || 'Failed to fetch appointments');
      }
      
      // Clear current container
      appointmentsContainer.innerHTML = '';
      
      // Add new cards
      if (data.appointments && data.appointments.length > 0) {
        console.log('Rendering ' + data.appointments.length + ' appointments');
        data.appointments.forEach((appointment) => {
          const card = createAppointmentCard(appointment);
          appointmentsContainer.appendChild(card);
        });
      } else {
        console.log('No appointments found');
        appointmentsContainer.innerHTML = `
          <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p>No appointments yet</p>
          </div>
        `;
      }
      
    } catch (error) {
      console.error('Error refreshing appointments:', error);
      appointmentsContainer.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">⚠️</div>
          <p>Error: ${error.message}</p>
        </div>
      `;
    } finally {
      loadingSpinner.classList.remove('show');
    }
  }

  function createAppointmentCard(appointment) {
    const card = document.createElement('div');
    card.className = 'appointment-card';
    
    const dateObj = new Date(appointment.date);
    const formattedDate = dateObj.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
    
    const [hours, minutes] = appointment.time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    const formattedTime = `${displayHour}:${minutes} ${ampm}`;

    card.innerHTML = `
      <div class="appointment-card-header">
        <h3 class="appointment-name">${escapeHtml(appointment.name)}</h3>
      </div>
      <div class="appointment-details">
        <div class="detail-item appointment-service">
          <span class="detail-label">Service</span>
          <span class="detail-value">${escapeHtml(appointment.service_name)}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Date</span>
          <span class="detail-value">${formattedDate}</span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Time</span>
          <span class="detail-value">${formattedTime}</span>
        </div>
      </div>
    `;
    
    return card;
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  // ========== Push Notification Subscription ==========
  async function subscribeToPush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.log('Push notifications not supported');
      return;
    }

    try {
      if (!vapidPublicKey) {
        const keyResponse = await fetch('http://localhost:3001/vapid-public-key');
        const keyData = await keyResponse.json();
        vapidPublicKey = keyData.publicKey;
      }

      if (!vapidPublicKey) {
        console.error('VAPID public key not available');
        return;
      }

      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        console.log('Notification permission denied');
        return;
      }

      const registration = await navigator.serviceWorker.ready;
      
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
      });

      const response = await fetch('register_push_subscription.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(subscription)
      });

      const result = await response.json();
      if (result.success) {
        console.log('Push subscription registered');
      }
    } catch (error) {
      console.error('Error subscribing to push:', error);
    }
  }

  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  // ========== Notification Management ==========
  async function loadUnreadCount() {
    try {
      const response = await fetch('get_unread_notifications.php');
      const data = await response.json();
      updateBadge(data.unread || 0);
    } catch (error) {
      console.error('Failed to load unread count:', error);
    }
  }

  async function markNotificationsSeen() {
    try {
      const response = await fetch('mark_notifications_seen.php');
      const data = await response.json();
      updateBadge(data.unread || 0);
    } catch (error) {
      console.error('Failed to mark notifications as seen:', error);
    }
  }

  // ========== Socket.IO Events ==========
  socket.on('booking_created', async (data) => {
    console.log('New booking received:', data);
    
    if (data.unreadCount !== undefined) {
      updateBadge(data.unreadCount);
    } else {
      const currentCount = parseInt(notificationBadge.textContent) || 0;
      updateBadge(currentCount + 1);
    }
    
    await refreshAppointments();
  });

  socket.on('booking_confirmed', async (data) => {
    console.log('Booking confirmed:', data);
    await refreshAppointments();
  });

  socket.on('connect', () => {
    console.log('Connected to real-time server');
  });

  socket.on('disconnect', () => {
    console.log('Disconnected from real-time server');
  });

  // ========== Initialize on Page Load ==========
  document.addEventListener('DOMContentLoaded', () => {
    loadUnreadCount();
    refreshAppointments();
    subscribeToPush();
    
    const appointmentsList = document.getElementById('appointments-container');
    if (appointmentsList) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            markNotificationsSeen();
          }
        });
      }, { threshold: 0.1 });
      
      observer.observe(appointmentsList);
    }
  });

  // ========== Logout with Cleanup ==========
  document.querySelector('a[href="logout.php"]')?.addEventListener('click', async () => {
    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.getSubscription();

      if (subscription) {
        await fetch('unregister_push_subscription.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ endpoint: subscription.endpoint })
        });
        await subscription.unsubscribe();
      }
    } catch (error) {
      console.error('Error during logout:', error);
    }
  });
</script>
</body>
</html>