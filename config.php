<?php
/**
 * Application Configuration
 * 
 * This file contains configuration settings that differ between
 * local development and production environments.
 * 
 * Environment variables take precedence over defaults.
 */

// Realtime Server URL (Node.js Socket.io server)
// Production: Set REALTIME_SERVER_URL environment variable
// Local: Falls back to http://localhost:3001
define('REALTIME_SERVER_URL', getenv('REALTIME_SERVER_URL') ?: 'http://localhost:3001');

// Notification token for authenticating with the Node.js server
define('NOTIFY_TOKEN', getenv('NOTIFY_TOKEN') ?: 'change-this-token');

// Helper function to get the realtime server URL for JavaScript
function get_realtime_server_url_js() {
    return REALTIME_SERVER_URL;
}
