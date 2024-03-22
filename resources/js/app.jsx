import './bootstrap';
import '../css/app.css';
import "@shopify/polaris/build/esm/styles.css";

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

import { AppProvider } from "@shopify/polaris";
import en from "@shopify/polaris/locales/en.json";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
        <AppProvider i18n={en}>
            <App {...props} />
        </AppProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
