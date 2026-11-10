import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      '@modules': path.resolve(__dirname, './resources/js/modules'),
      '@components': path.resolve(__dirname, './resources/js/components'),
      '@layouts': path.resolve(__dirname, './resources/js/layouts'),
    },
  },
  build: {
    outDir: 'public/build',
    manifest: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', '@inertiajs/react'],
          ui: ['lucide-react', 'clsx', 'tailwind-merge', 'class-variance-authority'],
        },
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    hmr: {
      host: 'localhost',
    },
  },
})