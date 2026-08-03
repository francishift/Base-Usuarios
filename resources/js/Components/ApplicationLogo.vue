<template>
    <img :src="src" class="dark:brightness-0 dark:invert transition-all" :class="$attrs.class" :alt="$page?.props?.appName || 'Logo'" />
</template>

<script setup>
const src = '/logo.svg';
</script>

