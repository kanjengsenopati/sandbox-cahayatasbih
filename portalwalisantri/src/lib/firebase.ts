import { initializeApp } from 'firebase/app';
import { getMessaging, getToken } from 'firebase/messaging';

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || 'pptq-cahaya-tasbih',
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID
};

// Inisialisasi Firebase App
const app = initializeApp(firebaseConfig);
export const messaging = getMessaging(app);

// Fungsi untuk mendapatkan Token FCM Browser PWA
export const getDeviceToken = async () => {
  try {
    const token = await getToken(messaging, {
      vapidKey: import.meta.env.VITE_FIREBASE_VAPID_PUBLIC_KEY
    });
    return token;
  } catch (error) {
    console.error("Gagal mendapatkan token FCM PWA:", error);
    return null;
  }
};
