<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    /** Variante de color: edit | activate | deactivate | delete | primary | secondary */
    variant: {
        type: String,
        default: 'primary',
    },
    /** Icono PrimeIcons (sin el prefijo 'pi '): 'pi-pencil', 'pi-trash', etc. */
    icon: {
        type: String,
        default: null,
    },
    /** Si se proporciona href, se renderiza como <Link> de Inertia */
    href: {
        type: String,
        default: null,
    },
    /** Método HTTP para Link de Inertia */
    method: {
        type: String,
        default: 'get',
    },
    /** Tipo de botón */
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    /** Tamaño: sm | md | lg */
    size: {
        type: String,
        default: 'sm',
    },
});

const emit = defineEmits(['click']);

const variantClasses = computed(() => {
    const variants = {
        edit:       'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-500/10',
        activate:   'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-500/10',
        restore:    'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-500/10',
        deactivate: 'text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:text-amber-300 dark:hover:bg-amber-500/10',
        delete:     'text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-500/10',
        primary:    'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-500/10',
        secondary:  'text-zinc-600 hover:text-zinc-800 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700',
    };
    return variants[props.variant] ?? variants.primary;
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'p-1.5 text-xs gap-1.5',
        md: 'p-2   text-sm gap-2',
        lg: 'p-2.5 text-base gap-2.5',
    };
    return sizes[props.size] ?? sizes.sm;
});

/** Tamaño del icono PrimeIcons vía font-size */
const iconFontSize = computed(() => {
    const sizes = { sm: 'text-sm', md: 'text-base', lg: 'text-lg' };
    return sizes[props.size] ?? sizes.sm;
});
</script>

<template>
    <!-- Renderiza como Link de Inertia si tiene href -->
    <Link
        v-if="href"
        :href="href"
        :method="method"
        :class="[
            'inline-flex items-center rounded-lg font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500',
            variantClasses,
            sizeClasses,
            disabled ? 'opacity-40 pointer-events-none' : '',
        ]"
    >
        <!-- Icono PrimeIcons vía prop -->
        <i v-if="icon" :class="['pi flex-shrink-0', icon, iconFontSize]" aria-hidden="true" />
        <!-- Icono personalizado vía slot (compatibilidad retroactiva) -->
        <span v-else-if="$slots.icon" class="flex-shrink-0" aria-hidden="true">
            <slot name="icon" />
        </span>
        <span v-if="$slots.default" class="leading-none">
            <slot />
        </span>
    </Link>

    <!-- Renderiza como botón si no tiene href -->
    <button
        v-else
        :type="type"
        :disabled="disabled"
        @click="emit('click')"
        :class="[
            'inline-flex items-center rounded-lg font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500',
            variantClasses,
            sizeClasses,
            disabled ? 'opacity-40 cursor-not-allowed' : '',
        ]"
    >
        <!-- Icono PrimeIcons vía prop -->
        <i v-if="icon" :class="['pi flex-shrink-0', icon, iconFontSize]" aria-hidden="true" />
        <!-- Icono personalizado vía slot (compatibilidad retroactiva) -->
        <span v-else-if="$slots.icon" class="flex-shrink-0" aria-hidden="true">
            <slot name="icon" />
        </span>
        <span v-if="$slots.default" class="leading-none">
            <slot />
        </span>
    </button>
</template>
