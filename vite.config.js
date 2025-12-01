import { PrimeVueResolver } from '@primevue/auto-import-resolver';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import Components from 'unplugin-vue-components/vite';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    laravel({
      input: ['resources/js/admin.js', 'resources/js/user.js'],
      refresh: true
    }),

    tailwindcss(),
    Components({
      resolvers: [PrimeVueResolver()]
    })
  ],
  build: {
    chunkSizeWarningLimit: 2000
  }
});
