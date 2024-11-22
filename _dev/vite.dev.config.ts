import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    fs: {
      strict: false
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler'
      }
    }
  },
  build: {
    rollupOptions: {
      input: {
        main: './src/ts/main.ts',
        theme: './src/scss/main.scss'
      },
      output: {
        dir: 'public'
      }
    }
  }
});
