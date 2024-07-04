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
      injectRegister: 'auto',
      workbox: {
        cleanupOutdatedCaches: true,
        globPatterns: ['**/*.{js,css,html,ico,png,jpg,jpeg,svg,json,vue,txt,woff2}']
      },
      manifest: {
        name: 'Griotte',
        short_name: 'Griotte',
        theme_color: '#ffffff',
        background_color: '#e83e3e',
        icons :[
          {
            purpose: "maskable",
            sizes: "512x512",
            src: "icon512_maskable.png",
            type: "image/png"
          },
          {
            purpose :"any",
            sizes :"512x512",
            src :"icon512_rounded.png",
            type :"image/png"
          }
        ],
      },

      devOptions: {
        enabled: true
      },
    
    })
],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})
