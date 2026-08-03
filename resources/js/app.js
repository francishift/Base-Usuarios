import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

/**
 * Tema personalizado: Aura con paleta Emerald (verde esmeralda) como primario
 * y Zinc (negro profundo) como superficie.
 * Aplica a todos los componentes PrimeVue: DataTable, Dialog, Button, Paginator, etc.
 */
const TemaArengalia = definePreset(Aura, {
    semantic: {
        // Paleta primaria: Emerald (Verde Esmeralda)
        primary: {
            50:  '#ecfdf5',
            100: '#d1fae5',
            200: '#a7f3d0',
            300: '#6ee7b7',
            400: '#34d399',
            500: '#10b981',
            600: '#059669',
            700: '#047857',
            800: '#065f46',
            900: '#064e3b',
            950: '#022c22',
        },
        // Paleta de superficies: Zinc (Negro mate / Pizarra oscura)
        colorScheme: {
            light: {
                surface: {
                    0:   '#ffffff',
                    50:  '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f46',
                    800: '#27272a',
                    900: '#18181b',
                    950: '#09090b',
                },
            },
            dark: {
                surface: {
                    0:   '#ffffff',
                    50:  '#fafafa',
                    100: '#f4f4f5',
                    200: '#e4e4e7',
                    300: '#d4d4d8',
                    400: '#a1a1aa',
                    500: '#71717a',
                    600: '#52525b',
                    700: '#3f3f46',
                    800: '#27272a',
                    900: '#18181b',
                    950: '#09090b',
                },
            },
        },
    },
});

createInertiaApp({
    title: (title) => {
        const defaultName = import.meta.env.VITE_APP_NAME || 'CMS SSR FRANCIS';
        return title ? `${title} - ${defaultName}` : defaultName;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: TemaArengalia,
                    options: { darkModeSelector: '.dark' },
                },
            })
            .use(ToastService)        // Notificaciones globales vía useToast()
            .use(ConfirmationService) // Diálogos de confirmación vía useConfirm()
            .directive('tooltip', Tooltip)
            .mount(el);
    },
    progress: {
        color: '#10b981', // emerald-500
    },
});
