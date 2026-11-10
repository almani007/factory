/**
 * Brixel FactoryOS — Vite Configuration (React 19 + Tailwind v4 + Laravel 13)
 */

import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import path from "path";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.tsx"],
      refresh: true,
    }),
    react(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./resources/js"),
      "@components": path.resolve(__dirname, "./resources/js/components"),
      "@features": path.resolve(__dirname, "./resources/js/features"),
      "@layouts": path.resolve(__dirname, "./resources/js/layouts"),
      "@modules": path.resolve(__dirname, "./resources/js/modules"),
      "@lib": path.resolve(__dirname, "./resources/js/lib"),
    },
  },
  build: {
    outDir: "public/build",
    manifest: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ["react", "react-dom", "@inertiajs/react"],
          ui: ["lucide-react", "clsx", "tailwind-merge", "class-variance-authority"],
        },
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    hmr: {
      host: "localhost",
    },
  },
})