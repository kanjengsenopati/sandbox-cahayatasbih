import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { TanStackRouterVite } from '@tanstack/router-plugin/vite';
import tsconfigPaths from 'vite-tsconfig-paths';
import { VitePWA } from 'vite-plugin-pwa';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  base: './',
  plugins: [
    TanStackRouterVite({
      routesDirectory: './src/routes',
      generatedRouteTree: './src/routeTree.gen.ts',
    }),
    tailwindcss(),
    react(),
    tsconfigPaths(),
    VitePWA({
      registerType: 'autoUpdate',
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
    outDir: 'dist/client',
    rollupOptions: {
      input: 'index.html',
    }
  }
});
