import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import os from "os";

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: process.env.LARAVEL_SAIL ? Object.values(os.networkInterfaces()).flat().find(info => info?.internal === false)?.address : undefined,
    },
});
