/**
 * Brixel FactoryOS — Main App Entry Point
 * React 19 + Inertia.js + Tailwind v4
 */

import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";

const pages = import.meta.glob("./features/**/*.tsx", { eager: true });

const pageModules = Object.fromEntries(
    Object.entries(pages).map(([path, module]) => [path.toLowerCase(), module])
);

createInertiaApp({
    resolve: (name) => {
        const [moduleName, pageName] = name.includes("/")
            ? name.split("/")
            : [name, name];

        const key = `./features/${moduleName.toLowerCase()}/${pageName.toLowerCase()}.tsx`;
        const page = pageModules[key];

        if (!page) {
            throw new Error(`Inertia page not found: ${name} (looked for ${key})`);
        }

        return page;
    },
    setup({ el, App, props }) {
        createRoot(el!).render(<App {...props} />);
    },
});
