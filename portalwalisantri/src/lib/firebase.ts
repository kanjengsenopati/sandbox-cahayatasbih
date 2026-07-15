import { initializeApp } from 'firebase/app';
import { getMessaging, getToken } from 'firebase/messaging';

const getFirebaseConfig = () => {
  const isBrowser = typeof window !== 'undefined';
  const win = isBrowser ? (window as any) : null;
  
  return {
    apiKey: win?.firebaseConfig?.apiKey || import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: win?.firebaseConfig?.authDomain || import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: win?.firebaseConfig?.projectId || import.meta.env.VITE_FIREBASE_PROJECT_ID || 'pptq-cahaya-tasbih',
    storageBucket: win?.firebaseConfig?.storageBucket || import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: win?.firebaseConfig?.messagingSenderId || import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: win?.firebaseConfig?.appId || import.meta.env.VITE_FIREBASE_APP_ID
  };
};

const firebaseConfig = getFirebaseConfig();

// Inisialisasi Firebase App
const app = initializeApp(firebaseConfig);
export const messaging = getMessaging(app);

// Fungsi untuk mendapatkan Token FCM Browser PWA
export const getDeviceToken = async () => {
  try {
    const isBrowser = typeof window !== 'undefined';
    const win = isBrowser ? (window as any) : null;
    const vapidKey = win?.firebaseConfig?.vapidKey || import.meta.env.VITE_FIREBASE_VAPID_PUBLIC_KEY;
    
    const token = await getToken(messaging, {
      vapidKey: vapidKey
    });
    return token;
  } catch (error) {
    console.error("Gagal mendapatkan token FCM PWA:", error);
    return null;
  }
};
