import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  root: 'assets/src/console',
  build: {
    outDir: '../../../assets/js/dist',
    emptyOutDir: false,
    rollupOptions: {
      input: 'assets/src/console/main.tsx',
      output: {
        entryFileNames: 'console.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return '../css/dist/console.css';
          }
          return '[name].[ext]';
        },
      },
    },
  },
  resolve: {
    alias: {
      '@console': path.resolve(__dirname, 'assets/src/console'),
    },
  },
});
