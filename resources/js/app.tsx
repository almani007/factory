/**
 * Brixel FactoryOS — Main App Entry Point
 * React 19 + Inertia.js + Tailwind v4
 */

import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { AppLayout } from "@/components/layout";

const appName = import.meta.env.VITE_APP_NAME || "Brixel FactoryOS";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./features/**/*.tsx", { eager: true });
        return pages[`./features/${name.charAt(0).toUpperCase() + name.slice(1)}/${name.charAt(0).toUpperCase() + name.slice(1)}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el!).render(
            <App {...props} layout={(page: any) => <AppLayout page={page} title={appName} />} />
        );
    },
});