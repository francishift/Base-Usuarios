<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page    = usePage();
const usuario = computed(() => page.props.auth.user);

const saludoHora = () => {
    const hora = new Date().getHours();
    if (hora < 12) return 'Buenos días';
    if (hora < 20) return 'Buenas tardes';
    return 'Buenas noches';
};
</script>

<template>
    <Head title="Panel de control" />

    <AuthenticatedLayout>
        <!-- Cabecera del topbar -->
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-white leading-none">
                    Panel de control
                </h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Bienvenido al sistema
                </p>
            </div>
        </template>

        <!-- Contenido -->
        <div class="p-6">

            <!-- Tarjeta de bienvenida -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900 shadow-sm p-6">
                <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ saludoHora() }}, <span class="text-emerald-600 dark:text-emerald-400">{{ usuario.name }}</span>
                </p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Has iniciado sesión correctamente en {{ $page.props.appName || 'el sistema' }}.
                </p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
