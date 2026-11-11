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
        // Support both simple names ("Dashboard") and compound names ("Accounting/ChartOfAccounts")
        const [moduleName, pageName] = name.includes("/")
            ? name.split("/")
            : [name, name];

        // Direct resolution: Dashboard → ./features/dashboard/dashboard.tsx
        // Compound: Accounting/ChartOfAccounts → ./features/accounting/chartofaccounts.tsx
        let key = `./features/${moduleName.toLowerCase()}/${pageName.toLowerCase()}.tsx`;
        let page = pageModules[key];

        // Fallback: split PascalCase compound names without slash
        // AccountingChartOfAccounts → module=Accounting, page=ChartOfAccounts
        if (!page && !name.includes("/")) {
            const match = name.match(/^[A-Z][a-z]+/);
            if (match) {
                const mod = match[0];
                const rest = name.slice(mod.length);
                const pg = rest || mod;
                key = `./features/${mod.toLowerCase()}/${pg.toLowerCase()}.tsx`;
                page = pageModules[key];
            }
        }

        if (!page) {
            throw new Error(`Inertia page not found: ${name} (looked for ${key})`);
        }

        return page;
    },
    setup({ el, App, props }) {
        createRoot(el!).render(<App {...props} />);
    },
});
