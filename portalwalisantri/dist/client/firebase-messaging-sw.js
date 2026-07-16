// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Konfigurasi Firebase Web App (Sesuaikan dengan data dari Firebase Console Anda)
const firebaseConfig = {
  apiKey: "AIzaSyCoWfvFlSBGw-SRBsLeS5F_Pm31Ry8hNMU",
  authDomain: "pwa-ct.firebaseapp.com",
  projectId: "pwa-ct",
  storageBucket: "pwa-ct.firebasestorage.app",
  messagingSenderId: "790487476334",
  appId: "1:790487476334:web:fd04e9e13d604b2c1fde7f"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Tangani notifikasi saat aplikasi berada di latar belakang (background)
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Notifikasi latar belakang diterima: ', payload);
  
  // Ambil title & body dari payload data atau notification
  const title = payload.data?.title || payload.notification?.title || "SantriPay";
  const body = payload.data?.body || payload.notification?.body || "Anda memiliki pemberitahuan baru.";
  
  const notificationOptions = {
    body: body,
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    data: payload.data
  };

  self.registration.showNotification(title, notificationOptions);
});

// Tangani klik pada notifikasi dengan Hash Routing support
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  // Tentukan URL tujuan berdasarkan type payload (contoh: tagihan, perizinan)
  let targetUrl = '/ct-mobile/app/#/';
  if (event.notification.data) {
    const type = event.notification.data.type;
    if (type === 'Transaction') {
      targetUrl = '/ct-mobile/app/#/tagihan';
    } else if (type === 'StudentPermit') {
      targetUrl = '/ct-mobile/app/#/perizinan';
    }
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Cari jika tab PWA sudah terbuka, navigasikan ke URL tujuan dan fokuskan
      for (const client of clientList) {
        if (client.url.includes('/ct-mobile/app') && 'focus' in client) {
          if ('navigate' in client) {
            client.navigate(targetUrl);
          }
          return client.focus();
        }
      }
      // Jika belum terbuka, buka tab baru
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
