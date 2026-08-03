<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-zinc-300 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-emerald-600 shadow-sm focus:ring-2 focus:ring-emerald-500/20 focus:ring-offset-0 dark:focus:ring-offset-zinc-950 transition cursor-pointer"
    />
</template>
