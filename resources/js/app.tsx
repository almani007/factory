/**
 * Brixel FactoryOS — Main App Entry Point
 * React 19 + Inertia.js + Tailwind v4
 */

import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-refresh";
import { AppLayout } from "@/components/layout";
import "./app.css";

const appName = import.meta.env.VITE_APP_NAME || "Brixel FactoryOS";

createInertiaApp({
  resolve: (name) =>
    resolvePageComponent(
      `./features/${name.charAt(0).toUpperCase() + name.slice(1)}/${name.charAt(0).toUpperCase() + name.slice(1)}.tsx`,
      import.meta.glob<{ default: React.ComponentType }>("./features/**/*.tsx")
    ),
  setup({ el, App, props }) {
    createRoot(el!).render(
      <App {...props} layout={(page: any) => <AppLayout page={page} title={appName} />} />
    );
  },
  progress: {
    color: "#0ea5e9",
  },
  title: (title) => `${appName} — ${title}`,
});