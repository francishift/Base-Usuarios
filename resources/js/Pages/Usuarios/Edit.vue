<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { useRoles } from '@/Composables/useRoles.js';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Select from 'primevue/select';

const props = defineProps({
    usuario: Object,
    roles:   Array,
});

const { etiqueta } = useRoles();

const form = useForm({
    name:     props.usuario.name,
    email:    props.usuario.email,
    rol:      props.usuario.rol,
    password: '',
});

const submit = () => {
    form.put(route('usuarios.update', props.usuario.id));
};
</script>

<template>
    <Head title="Editar usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-white leading-none">
                    Editar usuario
                </h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Modifica los datos, rol o contraseña de {{ usuario.name }}
                </p>
            </div>
        </template>

        <div class="p-6">
            <div class="mx-auto max-w-lg">

                <!-- Tarjeta del formulario -->
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900 shadow-sm">
                    <div class="px-6 py-5 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-500/10">
                                <i class="pi pi-user-edit text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Ficha de usuario</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ usuario.email }}</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="px-6 py-5 space-y-5">

                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="off"
                                class="block w-full rounded-lg border px-3.5 py-2.5 text-sm transition
                                    bg-white dark:bg-zinc-800
                                    text-zinc-900 dark:text-white
                                    placeholder-zinc-400 dark:placeholder-zinc-500
                                    border-zinc-300 dark:border-zinc-700
                                    focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400/20': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[11px]" />
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Correo electrónico <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="off"
                                class="block w-full rounded-lg border px-3.5 py-2.5 text-sm transition
                                    bg-white dark:bg-zinc-800
                                    text-zinc-900 dark:text-white
                                    placeholder-zinc-400 dark:placeholder-zinc-500
                                    border-zinc-300 dark:border-zinc-700
                                    focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-400/20': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[11px]" />
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="rol" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <Select
                                id="rol"
                                v-model="form.rol"
                                :options="roles"
                                :option-label="(r) => etiqueta(r)"
                                :option-value="(r) => r"
                                placeholder="Selecciona un rol…"
                                class="w-full"
                                :invalid="!!form.errors.rol"
                            />
                            <p v-if="form.errors.rol" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[11px]" />
                                {{ form.errors.rol }}
                            </p>
                        </div>

                        <!-- Nueva contraseña opcional -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Nueva contraseña <span class="text-xs text-zinc-400 font-normal">(opcional)</span>
                            </label>
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                placeholder="•••••••• (dejar en blanco para no modificar)"
                                autocomplete="new-password"
                            />
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[11px]" />
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <Link
                                :href="route('usuarios.index')"
                                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                            >
                                ← Cancelar
                            </Link>

                            <Button
                                type="submit"
                                label="Guardar cambios"
                                icon="pi pi-check"
                                icon-pos="right"
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
