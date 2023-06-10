import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'Griotte',
        short_name: 'Griotte',
        theme_color: '#ffffff',
        icons: [
          {
            src: '/img/logo.svg',
            sizes: '192x192',
            type: 'image/svg'
          },
          {
            src: '/img/logo.svg',
            sizes: '512x512',
            type: 'image/svg'
          }
        ]
      },

      devOptions: {
        enabled: true
      },
      workbox: {
        globPatterns: [
          '**/*.{js,css,html,json,ico,png,jpg,jpeg,svg}'
        ],
        cleanupOutdatedCaches: true,
      }
    })
],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})
