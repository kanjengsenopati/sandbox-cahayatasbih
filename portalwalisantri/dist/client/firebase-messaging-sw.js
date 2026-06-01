// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

// Konfigurasi Firebase Web App (Sesuaikan dengan data dari Firebase Console Anda)
const firebaseConfig = {
  apiKey: "YOUR_API_KEY_HERE",
  authDomain: "pptq-cahaya-tasbih.firebaseapp.com",
  projectId: "pptq-cahaya-tasbih",
  storageBucket: "pptq-cahaya-tasbih.appspot.com",
  messagingSenderId: "YOUR_SENDER_ID_HERE",
  appId: "YOUR_APP_ID_HERE"
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

// Tangani klik pada notifikasi
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  // Tentukan URL tujuan berdasarkan type payload (contoh: tagihan, tabungan)
  let targetUrl = '/ct-mobile/app/';
  if (event.notification.data && event.notification.data.type === 'Transaction') {
    targetUrl = '/ct-mobile/app/tagihan';
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Cari jika tab PWA sudah terbuka, cukup fokuskan tab tersebut
      for (const client of clientList) {
        if (client.url.includes('/ct-mobile/app') && 'focus' in client) {
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
