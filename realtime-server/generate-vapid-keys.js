// generate-vapid-keys.js
// Run this script to generate VAPID keys: node generate-vapid-keys.js

const webpush = require('web-push');

const vapidKeys = webpush.generateVAPIDKeys();

console.log('VAPID Keys Generated:');
console.log('Public Key:', vapidKeys.publicKey);
console.log('Private Key:', vapidKeys.privateKey);
console.log('\nAdd these to your environment variables or config file.');
console.log('Public key will be sent to browsers for subscription.');
console.log('Private key must stay server-side only!');

// Optionally save to file (be careful with private key!)
const fs = require('fs');
const keys = {
  publicKey: vapidKeys.publicKey,
  privateKey: vapidKeys.privateKey
};

fs.writeFileSync('vapid-keys.json', JSON.stringify(keys, null, 2));
console.log('\nKeys saved to vapid-keys.json (keep this file secure!)');

