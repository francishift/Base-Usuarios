<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { useRoles } from '@/Composables/useRoles.js';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Select from 'primevue/select';

defineProps({
    roles: Array,
});

const { etiqueta } = useRoles();

const form = useForm({
    name:     '',
    email:    '',
    rol:      '',
    password: '',
});

const copiado = ref(false);

/**
 * Genera una contraseña segura de 12 caracteres cumpliendo los requisitos estrictos:
 * Mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos especiales.
 */
function generarContrasena() {
    const mayus = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const minus = 'abcdefghijkmnopqrstuvwxyz';
    const nums  = '23456789';
    const sims  = '!@#$%^&*()_+-=';
    
    let pass = [
        mayus.charAt(Math.floor(Math.random() * mayus.length)),
        minus.charAt(Math.floor(Math.random() * minus.length)),
        nums.charAt(Math.floor(Math.random() * nums.length)),
        sims.charAt(Math.floor(Math.random() * sims.length)),
    ];

    const todos = mayus + minus + nums + sims;
    for (let i = 4; i < 12; i++) {
        pass.push(todos.charAt(Math.floor(Math.random() * todos.length)));
    }

    // Mezcla Fisher-Yates
    for (let i = pass.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [pass[i], pass[j]] = [pass[j], pass[i]];
    }

    form.password = pass.join('');
}

/**
 * Copia la contraseña actual al portapapeles.
 */
async function copiarContrasena() {
    if (!form.password) return;
    try {
        await navigator.clipboard.writeText(form.password);
        copiado.value = true;
        setTimeout(() => { copiado.value = false; }, 2000);
    } catch (e) {
        console.error('No se pudo copiar la contraseña:', e);
    }
}

const submit = () => form.post(route('usuarios.store'));
</script>

<template>
    <Head title="Nuevo usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-zinc-900 dark:text-white leading-none">
                    Nuevo usuario
                </h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Crea una nueva cuenta y envía las credenciales por email
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
                                <i class="pi pi-user-plus text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Datos del nuevo usuario</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Completa la información requerida</p>
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
                                autocomplete="off"
                                autofocus
                                required
                                placeholder="Ej: María García López"
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
                                autocomplete="off"
                                required
                                placeholder="usuario@empresa.com"
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

                        <!-- Contraseña -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                    Contraseña <span class="text-xs text-zinc-400 font-normal">(opcional)</span>
                                </label>
                                <button
                                    type="button"
                                    @click="generarContrasena"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold
                                           text-emerald-600 dark:text-emerald-400 hover:text-emerald-700
                                           hover:bg-emerald-50 dark:hover:bg-emerald-500/10 px-2 py-1 rounded-md transition-colors"
                                >
                                    <i class="pi pi-key text-[11px]" /> Generar contraseña segura
                                </button>
                            </div>

                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                placeholder="Escribe o genera una contraseña…"
                                autocomplete="new-password"
                            />
                            <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                Si la dejas en blanco, el sistema generará una clave aleatoria automáticamente.
                            </p>
                            <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[11px]" />
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Caja informativa de la contraseña establecida / generada -->
                        <div
                            v-if="form.password"
                            class="flex items-center justify-between rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3"
                        >
                            <div class="min-w-0 flex-1 mr-3">
                                <p class="text-xs text-emerald-800 dark:text-emerald-300 font-medium">Contraseña a asignar:</p>
                                <p class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400 truncate tracking-wide">
                                    {{ form.password }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="copiarContrasena"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/30 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-zinc-700 transition"
                            >
                                <i :class="['pi text-xs', copiado ? 'pi-check text-emerald-500' : 'pi-copy']" />
                                <span>{{ copiado ? '¡Copiado!' : 'Copiar' }}</span>
                            </button>
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <Link
                                :href="route('usuarios.index')"
                                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                            >
                                ← Volver al listado
                            </Link>

                            <Button
                                type="submit"
                                label="Crear usuario y enviar email"
                                icon="pi pi-send"
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
