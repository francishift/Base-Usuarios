<script setup>
import { ref } from 'vue';

/**
 * Componente de input de contraseña con toggle de visibilidad.
 * Reutilizable en cualquier formulario del proyecto.
 *
 * Uso:
 *   <PasswordInput id="password" v-model="form.password" />
 */
const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        required: true,
    },
    autocomplete: {
        type: String,
        default: 'current-password',
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    class: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

/** Controla si la contraseña es visible o no */
const visible = ref(false);
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            :type="visible ? 'text' : 'password'"
            :value="modelValue"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            @input="emit('update:modelValue', $event.target.value)"
            class="block w-full px-3.5 py-2.5 text-sm rounded-lg border border-zinc-300 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white pr-10 shadow-sm placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition"
            :class="[disabled ? 'opacity-50 cursor-not-allowed' : '']"
        />

        <!-- Botón toggle mostrar/ocultar contraseña -->
        <button
            type="button"
            @click="visible = !visible"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors"
            :title="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            tabindex="-1"
        >
            <i :class="['pi text-base', visible ? 'pi-eye-slash' : 'pi-eye']" />
        </button>
    </div>
</template>
