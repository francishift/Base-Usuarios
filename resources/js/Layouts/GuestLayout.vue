<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

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
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center bg-zinc-100 dark:bg-zinc-950 px-4 py-8 text-zinc-900 dark:text-zinc-100 transition-colors duration-300">
        <!-- Botón toggle de modo claro/oscuro en esquina superior derecha -->
        <div class="absolute top-4 right-4">
            <button
                @click="toggleModoOscuro"
                type="button"
                class="rounded-xl p-2.5 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-800 transition-colors border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm"
                :title="modoOscuro ? 'Activar modo claro' : 'Activar modo oscuro'"
                :aria-label="modoOscuro ? 'Activar modo claro' : 'Activar modo oscuro'"
            >
                <i :class="['text-lg', modoOscuro ? 'pi pi-sun' : 'pi pi-moon']" />
            </button>
        </div>

        <div class="mb-6">
            <Link href="/" class="flex flex-col items-center gap-2">
                <ApplicationLogo class="h-16 w-auto drop-shadow-sm" />
            </Link>
        </div>

        <div class="w-full sm:max-w-md overflow-hidden bg-white dark:bg-zinc-900 px-6 py-8 border border-zinc-200 dark:border-zinc-800/80 shadow-2xl rounded-2xl transition-colors duration-300">
            <slot />
        </div>
    </div>
</template>

