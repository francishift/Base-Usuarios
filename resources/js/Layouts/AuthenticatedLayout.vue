<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';

const page    = usePage();
const usuario = computed(() => page.props.auth?.user);
const esAdmin = computed(() => usuario.value?.roles?.includes('admin'));

const toast = useToast();

// Convierte los mensajes flash de Inertia en Toasts de PrimeVue
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({ severity: 'success', summary: 'Correcto', detail: flash.success, life: 4000 });
        }
        if (flash?.error) {
            toast.add({ severity: 'error', summary: 'Error', detail: flash.error, life: 5000 });
        }
    },
    { deep: true },
);

/* ─── Sidebar móvil ────────────────────────────────── */
const sidebarAbierto = ref(false);

function cerrarSidebar() {
    sidebarAbierto.value = false;
}

/* ─── Modo oscuro — persiste en localStorage ───────── */
const modoOscuro = ref(false);

onMounted(() => {
    const guardado = localStorage.getItem('modo-oscuro');
    modoOscuro.value = guardado === null ? document.documentElement.classList.contains('dark') : guardado === 'true';
    aplicarModo();
});

function toggleModoOscuro() {
    modoOscuro.value = !modoOscuro.value;
    localStorage.setItem('modo-oscuro', modoOscuro.value);
    aplicarModo();
}

function aplicarModo() {
    document.documentElement.classList.toggle('dark', modoOscuro.value);
}

/* ─── Navegación ────────────────────────────────────── */
const navItems = computed(() => [
    {
        label: 'Panel de control',
        icon:  'pi pi-home',
        route: 'dashboard',
        activo: route().current('dashboard'),
    },
    ...(esAdmin.value ? [{
        label: 'Usuarios',
        icon:  'pi pi-users',
        route: 'usuarios.index',
        activo: route().current('usuarios.*'),
    }] : []),
]);

/* ─── Utilidades ────────────────────────────────────── */
function getIniciales(nombre) {
    return nombre
        ?.split(' ')
        .slice(0, 2)
        .map(n => n[0])
        .join('')
        .toUpperCase() ?? 'U';
}

function cerrarSesion() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-zinc-100 dark:bg-zinc-950 transition-colors duration-300">

        <!-- Toasts globales (mensajes flash de Inertia) -->
        <Toast position="bottom-right" />
        <!-- Diálogo de confirmación global -->
        <ConfirmDialog />
        <!-- Overlay oscuro para móvil -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarAbierto"
                class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm lg:hidden"
                @click="cerrarSidebar"
            />
        </Transition>

        <!-- ══════════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════════ -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-zinc-950 border-r border-zinc-800/80 shadow-2xl',
                'transition-transform duration-300 ease-in-out lg:static lg:translate-x-0',
                sidebarAbierto ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo y nombre de la app -->
            <div class="flex h-16 items-center gap-3 border-b border-zinc-800/80 px-5">
                <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0">
                    <!-- Contenedor emerald con logo SVG invertido a blanco -->
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-600 shadow-lg shadow-emerald-600/30">
                        <ApplicationLogo class="h-5 w-5 brightness-0 invert" />
                    </div>
                    <span class="truncate text-sm font-semibold text-white tracking-tight">
                        {{ $page.props.appName ?? 'CMS SSR' }}
                    </span>
                </Link>
            </div>

            <!-- Menú de navegación -->
            <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-0.5">
                <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-widest text-zinc-500">
                    Menú
                </p>

                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    @click="cerrarSidebar"
                    :class="[
                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
                        item.activo
                            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25'
                            : 'text-zinc-400 hover:bg-zinc-900 hover:text-white',
                    ]"
                >
                    <i
                        :class="[
                            item.icon,
                            'flex-shrink-0 text-[15px] transition-transform duration-150',
                            !item.activo && 'group-hover:scale-110',
                        ]"
                    />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- Info y acciones del usuario autenticado -->
            <div class="border-t border-zinc-800/80 p-3 space-y-0.5">
                <!-- Perfil -->
                <Link
                    :href="route('profile.edit')"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-zinc-400 hover:bg-zinc-900 hover:text-white transition-all duration-150"
                >
                    <!-- Avatar con iniciales -->
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-600/20 ring-1 ring-emerald-500/30 text-emerald-400 text-xs font-bold">
                        {{ getIniciales(usuario?.name) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-200 leading-none">
                            {{ usuario?.name }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-zinc-500 leading-none">
                            {{ usuario?.email }}
                        </p>
                    </div>
                    <i class="pi pi-chevron-right text-xs text-zinc-600 group-hover:text-zinc-400 flex-shrink-0" />
                </Link>

                <!-- Cerrar sesión -->
                <button
                    @click="cerrarSesion"
                    class="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-zinc-500 hover:bg-red-500/10 hover:text-red-400 transition-all duration-150"
                >
                    <i class="pi pi-sign-out flex-shrink-0 text-[15px]" />
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- ══════════════════════════════════════════════
             ÁREA DE CONTENIDO
        ═══════════════════════════════════════════════ -->
        <div class="flex flex-1 flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="flex h-16 items-center gap-4 border-b border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900 px-4 lg:px-6 flex-shrink-0 transition-colors duration-300 shadow-sm">

                <!-- Botón hamburger — solo en móvil -->
                <button
                    @click="sidebarAbierto = !sidebarAbierto"
                    class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors lg:hidden"
                    aria-label="Abrir menú"
                >
                    <i class="pi pi-bars text-lg" />
                </button>

                <!-- Título / breadcrumb de la página -->
                <div class="flex-1 min-w-0">
                    <slot name="header" />
                </div>

                <!-- Acciones lado derecho -->
                <div class="flex items-center gap-1">
                    <!-- Toggle modo oscuro / claro -->
                    <button
                        @click="toggleModoOscuro"
                        class="rounded-lg p-2 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                        :title="modoOscuro ? 'Activar modo claro' : 'Activar modo oscuro'"
                        :aria-label="modoOscuro ? 'Activar modo claro' : 'Activar modo oscuro'"
                    >
                        <i :class="['text-lg', modoOscuro ? 'pi pi-sun' : 'pi pi-moon']" />
                    </button>
                </div>
            </header>

            <!-- Contenido de la página -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
