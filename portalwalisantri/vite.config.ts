import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { TanStackRouterVite } from '@tanstack/router-plugin/vite';
import tsconfigPaths from 'vite-tsconfig-paths';
import { VitePWA } from 'vite-plugin-pwa';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  base: '/portalwalisantri/dist/',
  envDir: '../', // Read environment variables from Laravel root .env
  plugins: [
    TanStackRouterVite({
      routesDirectory: './src/routes',
      generatedRouteTree: './src/routeTree.gen.ts',
      autoCodeSplitting: true,
    }),
    tailwindcss(),
    react(),
    tsconfigPaths(),
    VitePWA({
      registerType: 'autoUpdate',
      workbox: {
        cleanupOutdatedCaches: true,
        skipWaiting: true,
        clientsClaim: true,
        inlineWorkboxRuntime: true,
        navigateFallback: null,
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/fonts\.(?:googleapis|gstatic)\.com\/.*/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts-cache',
              expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
            },
          },
          {
            urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|ico)$/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'pwa-images-cache',
              expiration: { maxEntries: 50, maxAgeSeconds: 60 * 60 * 24 * 30 },
            },
          },
          {
            urlPattern: /\.(?:js|css)$/i,
            handler: 'StaleWhileRevalidate',
            options: {
              cacheName: 'pwa-static-resources-v2',
            },
          },
        ],
      },
      manifest: {
        name: 'CT-Mobile',
        short_name: 'CT-Mobile',
        start_url: '/ct-mobile/app',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#9b1de8',
        icons: [
          { src: '/icons/icon-192.png?v=4', sizes: '192x192', type: 'image/png' },
          { src: '/icons/icon-512.png?v=4', sizes: '512x512', type: 'image/png' }
        ]
      }
    })
  ],
  build: {
    manifest: 'vite-manifest.json',
    outDir: '../public/portalwalisantri/dist',
    emptyOutDir: true,
    rollupOptions: {
      input: 'index.html',
    }
  }
});
