<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ActionButton from '@/Components/ActionButton.vue';
import { useRoles } from '@/Composables/useRoles.js';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Button from 'primevue/button';
import { useConfirm } from 'primevue/useconfirm';

import PasswordInput from '@/Components/PasswordInput.vue';

const props = defineProps({
    usuarios: Object,       // Paginación de Laravel via Inertia
    filtros:  Object,       // Parámetros activos de búsqueda y ordenación
    roles:    Array,        // Roles disponibles para el formulario de edición
    esPapelera: Boolean,   // Indica si estamos en la vista de papelera
    conteoPapelera: Number, // Número de registros en la papelera
});

const confirm = useConfirm();
const { etiqueta, color } = useRoles();

/* ─── Búsqueda con debounce ─────────────────────────── */
const busqueda = ref(props.filtros?.search ?? '');
let timeoutBusqueda = null;

watch(busqueda, () => {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(() => {
        cargar({ search: busqueda.value, page: 1 });
    }, 400);
});

/* ─── Estado lazy del DataTable ─────────────────────── */
const lazyParams = ref({
    first:     (props.usuarios.meta.current_page - 1) * props.usuarios.meta.per_page,
    rows:      props.usuarios.meta.per_page,
    sortField: props.filtros?.sortField ?? 'name',
    sortOrder: props.filtros?.sortOrder === 'desc' ? -1 : 1,
});

/* ─── Navegación server-side ────────────────────────── */
function cargar(params = {}) {
    const rutaTarget = props.esPapelera ? 'usuarios.trashed' : 'usuarios.index';
    router.get(
        route(rutaTarget),
        {
            search:    busqueda.value,
            sortField: lazyParams.value.sortField,
            sortOrder: lazyParams.value.sortOrder === -1 ? 'desc' : 'asc',
            page:      Math.floor(lazyParams.value.first / lazyParams.value.rows) + 1,
            ...params,
        },
        { preserveScroll: true, preserveState: true },
    );
}

function onPage(event) {
    lazyParams.value = { ...lazyParams.value, first: event.first, rows: event.rows };
    cargar({ page: Math.floor(event.first / event.rows) + 1 });
}

function onSort(event) {
    lazyParams.value = { ...lazyParams.value, sortField: event.sortField, sortOrder: event.sortOrder, first: 0 };
    cargar({ sortField: event.sortField, sortOrder: event.sortOrder === -1 ? 'desc' : 'asc', page: 1 });
}

/* ─── Dialog de edición inline ──────────────────────── */
const dialogVisible  = ref(false);
const guardando      = ref(false);
const erroresForm    = ref({});

const form = reactive({ id: null, name: '', email: '', rol: '', password: '' });

/** Abre el Dialog con los datos del usuario seleccionado */
function editarUsuario(event) {
    if (props.esPapelera) return;
    const u = event.data;
    form.id       = u.id;
    form.name     = u.name;
    form.email    = u.email;
    form.rol      = u.rol ?? '';
    form.password = '';
    erroresForm.value = {};
    dialogVisible.value = true;
}

/** Guarda los cambios vía PUT */
function guardarUsuario() {
    guardando.value = true;
    erroresForm.value = {};

    router.put(
        route('usuarios.update', form.id),
        { name: form.name, email: form.email, rol: form.rol, password: form.password },
        {
            preserveScroll: true,
            onSuccess: () => {
                dialogVisible.value = false;
            },
            onError: (errors) => {
                erroresForm.value = errors;
            },
            onFinish: () => {
                guardando.value = false;
            },
        },
    );
}

/* ─── Activar / Desactivar ──────────────────────────── */
const toggleActivo = (usuario) => {
    router.patch(route('usuarios.toggle-active', usuario.id), {}, { preserveScroll: true });
};

/* ─── Eliminar a la Papelera (Soft Delete) ──────────── */
function confirmarEliminar(usuario) {
    confirm.require({
        message:       `¿Seguro que quieres enviar a la papelera a "${usuario.name}"? podrás restaurarlo en cualquier momento.`,
        header:        'Enviar a la papelera',
        icon:          'pi pi-trash',
        rejectLabel:   'Cancelar',
        acceptLabel:   'Sí, enviar a papelera',
        acceptClass:   'p-button-danger',
        accept: () => {
            router.delete(route('usuarios.destroy', usuario.id));
        },
    });
}

/* ─── Restaurar usuario de la Papelera ──────────────── */
function restaurarUsuario(usuario) {
    router.patch(route('usuarios.restore', usuario.id), {}, { preserveScroll: true });
}

/* ─── Eliminar Definitivamente (Force Delete) ───────── */
function confirmarEliminarDefinitivo(usuario) {
    confirm.require({
        message:       `¿ATENCIÓN! ¿Eliminar definitivamente a "${usuario.name}"? Esta acción se borrará de forma PERMANENTE de la base de datos y no se podrá deshacer.`,
        header:        'Eliminación permanente',
        icon:          'pi pi-exclamation-triangle',
        rejectLabel:   'Cancelar',
        acceptLabel:   'Sí, eliminar de forma permanente',
        acceptClass:   'p-button-danger',
        accept: () => {
            router.delete(route('usuarios.force-delete', usuario.id), { preserveScroll: true });
        },
    });
}
</script>

<template>
    <Head :title="esPapelera ? 'Papelera de usuarios' : 'Gestión de usuarios'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-lg font-semibold text-zinc-900 dark:text-white leading-none">
                        {{ esPapelera ? 'Papelera de usuarios' : 'Gestión de usuarios' }}
                    </h1>
                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ esPapelera ? 'Usuarios eliminados suavemente que pueden ser restaurados' : 'Administra los usuarios del sistema' }}
                    </p>
                </div>

                <!-- Pestañas Activos / Papelera -->
                <div class="flex items-center gap-1 rounded-xl bg-zinc-200 dark:bg-zinc-800/80 p-1 text-xs">
                    <Link
                        :href="route('usuarios.index')"
                        class="flex items-center gap-2 rounded-lg px-3.5 py-1.5 font-medium transition-all"
                        :class="!esPapelera ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                    >
                        <i class="pi pi-users text-xs" />
                        <span>Usuarios activos</span>
                    </Link>

                    <Link
                        :href="route('usuarios.trashed')"
                        class="flex items-center gap-2 rounded-lg px-3.5 py-1.5 font-medium transition-all"
                        :class="esPapelera ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                    >
                        <i class="pi pi-trash text-xs" />
                        <span>Papelera</span>
                        <span
                            v-if="conteoPapelera > 0"
                            class="rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold px-1.5 py-0.2"
                        >
                            {{ conteoPapelera }}
                        </span>
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6 space-y-4">

            <!-- Buscador visible en móvil -->
            <div class="flex items-center gap-3 md:hidden">
                <IconField class="flex-1">
                    <InputIcon class="pi pi-search" />
                    <InputText v-model="busqueda" placeholder="Buscar…" class="w-full" />
                </IconField>
                <Button
                    v-if="!esPapelera"
                    as="a"
                    :href="route('usuarios.create')"
                    icon="pi pi-user-plus"
                    v-tooltip.left="'Nuevo usuario'"
                />
            </div>

            <!-- ═════════════════════════════════════════════
                 TARJETAS MÓVIL (< md)
            ═════════════════════════════════════════════ -->
            <div class="block md:hidden space-y-3">

                <!-- Sin resultados en móvil -->
                <div v-if="!usuarios.data.length" class="flex flex-col items-center justify-center py-16">
                    <i class="pi pi-users text-4xl text-zinc-300 dark:text-zinc-700" />
                    <p class="mt-3 text-sm text-zinc-400 dark:text-zinc-500">
                        {{ busqueda ? 'Sin resultados para esa búsqueda.' : (esPapelera ? 'La papelera está vacía.' : 'No hay usuarios registrados.') }}
                    </p>
                </div>

                <!-- Card de usuario -->
                <div
                    v-for="usuario in usuarios.data"
                    :key="usuario.id"
                    @click="editarUsuario({ data: usuario })"
                    class="group rounded-xl border border-zinc-200 dark:border-zinc-800/80
                           bg-white dark:bg-zinc-900 shadow-sm px-4 py-3.5
                           transition-all duration-150"
                    :class="[esPapelera ? '' : 'cursor-pointer hover:border-emerald-500/40 hover:shadow-md']"
                >
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full
                                    bg-emerald-100 dark:bg-emerald-500/10
                                    text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                            {{ usuario.name?.charAt(0)?.toUpperCase() }}
                        </div>

                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                {{ usuario.name }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">
                                {{ usuario.email }}
                            </p>
                        </div>

                        <!-- Badges -->
                        <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold" :class="color(usuario.rol)">
                                {{ etiqueta(usuario.rol) }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="usuario.active
                                    ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                                    : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'"
                            >
                                <i :class="['pi text-[9px]', usuario.active ? 'pi-circle-fill' : 'pi-circle']" />
                                {{ usuario.active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>

                    <!-- Acciones en móvil -->
                    <div class="flex items-center justify-end gap-1 mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80" @click.stop>
                        <template v-if="!esPapelera">
                            <ActionButton variant="edit" icon="pi-pencil" @click="editarUsuario({ data: usuario })" v-tooltip.top="'Editar'" />
                            <ActionButton :variant="usuario.active ? 'deactivate' : 'activate'" :icon="usuario.active ? 'pi-ban' : 'pi-check-circle'" @click="toggleActivo(usuario)" v-tooltip.top="usuario.active ? 'Desactivar' : 'Activar'" />
                            <ActionButton variant="delete" icon="pi-trash" @click="confirmarEliminar(usuario)" v-tooltip.top="'Enviar a papelera'" />
                        </template>
                        <template v-else>
                            <ActionButton variant="restore" icon="pi-undo" @click="restaurarUsuario(usuario)" v-tooltip.top="'Restaurar usuario'" />
                            <ActionButton variant="delete" icon="pi-trash" @click="confirmarEliminarDefinitivo(usuario)" v-tooltip.top="'Eliminar definitivamente'" />
                        </template>
                    </div>
                </div>

                <!-- Paginación móvil simple -->
                <div v-if="usuarios.meta.last_page > 1" class="flex items-center justify-between pt-2">
                    <button
                        :disabled="!usuarios.links?.prev"
                        @click="cargar({ page: usuarios.meta.current_page - 1 })"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium
                               text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800
                               disabled:opacity-40 disabled:pointer-events-none transition-colors"
                    >
                        <i class="pi pi-chevron-left text-xs" /> Anterior
                    </button>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        Página {{ usuarios.meta.current_page }} de {{ usuarios.meta.last_page }}
                    </span>
                    <button
                        :disabled="!usuarios.links?.next"
                        @click="cargar({ page: usuarios.meta.current_page + 1 })"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium
                               text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800
                               disabled:opacity-40 disabled:pointer-events-none transition-colors"
                    >
                        Siguiente <i class="pi pi-chevron-right text-xs" />
                    </button>
                </div>
            </div>

            <!-- ═════════════════════════════════════════════
                 DATATABLE DESKTOP (≥ md)
            ═════════════════════════════════════════════ -->
            <div class="hidden md:block">
            <DataTable
                :value="usuarios.data"
                :lazy="true"
                :paginator="usuarios.meta.last_page > 1"
                :rows="lazyParams.rows"
                :total-records="usuarios.meta.total"
                :first="lazyParams.first"
                :sort-field="lazyParams.sortField"
                :sort-order="lazyParams.sortOrder"
                @page="onPage"
                @sort="onSort"
                @row-click="editarUsuario"
                row-hover
                data-key="id"
                striped-rows
                removable-sort
                :rows-per-page-options="[10, 20, 50]"
                paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                current-page-report-template="Mostrando {first}–{last} de {totalRecords} usuarios"
                :pt="{ bodyRow: { class: esPapelera ? '' : 'cursor-pointer' } }"
            >
                <!-- Cabecera: buscador + botón nuevo usuario -->
                <template #header>
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <IconField>
                            <InputIcon class="pi pi-search" />
                            <InputText
                                v-model="busqueda"
                                placeholder="Buscar por nombre o email…"
                                class="w-72"
                            />
                        </IconField>

                        <!-- Botón nuevo usuario sólo si no estamos en papelera -->
                        <Button
                            v-if="!esPapelera"
                            as="a"
                            :href="route('usuarios.create')"
                            label="Nuevo usuario"
                            icon="pi pi-user-plus"
                        />
                    </div>
                </template>

                <!-- Sin resultados -->
                <template #empty>
                    <div class="flex flex-col items-center justify-center py-12">
                        <i class="pi pi-users text-4xl text-zinc-300 dark:text-zinc-700" />
                        <p class="mt-3 text-sm text-zinc-400 dark:text-zinc-500">
                            {{ busqueda ? 'No se encontraron usuarios con esa búsqueda.' : (esPapelera ? 'La papelera está vacía.' : 'No hay usuarios registrados.') }}
                        </p>
                    </div>
                </template>

                <!-- Nombre con avatar -->
                <Column field="name" header="Nombre" sortable>
                    <template #body="{ data }">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                                {{ data.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ data.name }}</span>
                        </div>
                    </template>
                </Column>

                <!-- Email -->
                <Column field="email" header="Correo" sortable>
                    <template #body="{ data }">
                        <span class="text-zinc-500 dark:text-zinc-400">{{ data.email }}</span>
                    </template>
                </Column>

                <!-- Rol -->
                <Column header="Rol">
                    <template #body="{ data }">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="color(data.rol)">
                            {{ etiqueta(data.rol) }}
                        </span>
                    </template>
                </Column>

                <!-- Estado -->
                <Column field="active" header="Estado" sortable>
                    <template #body="{ data }">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="data.active
                                ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                                : 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'"
                        >
                            <i :class="['pi text-[10px]', data.active ? 'pi-circle-fill' : 'pi-circle']" />
                            {{ data.active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </template>
                </Column>

                <!-- Acciones (detiene el row-click con @click.stop) -->
                <Column header="Acciones" :exportable="false" style="width: 120px">
                    <template #body="{ data }">
                        <div class="flex items-center justify-end gap-1" @click.stop>
                            <template v-if="!esPapelera">
                                <ActionButton
                                    variant="edit"
                                    icon="pi-pencil"
                                    @click="editarUsuario({ data })"
                                    v-tooltip.top="'Editar usuario'"
                                />
                                <ActionButton
                                    :variant="data.active ? 'deactivate' : 'activate'"
                                    :icon="data.active ? 'pi-ban' : 'pi-check-circle'"
                                    @click="toggleActivo(data)"
                                    v-tooltip.top="data.active ? 'Desactivar usuario' : 'Activar usuario'"
                                />
                                <ActionButton
                                    variant="delete"
                                    icon="pi-trash"
                                    @click="confirmarEliminar(data)"
                                    v-tooltip.top="'Enviar a papelera'"
                                />
                            </template>
                            <template v-else>
                                <ActionButton
                                    variant="restore"
                                    icon="pi-undo"
                                    @click="restaurarUsuario(data)"
                                    v-tooltip.top="'Restaurar usuario'"
                                />
                                <ActionButton
                                    variant="delete"
                                    icon="pi-trash"
                                    @click="confirmarEliminarDefinitivo(data)"
                                    v-tooltip.top="'Eliminar definitivamente'"
                                />
                            </template>
                        </div>
                    </template>
                </Column>
            </DataTable>
            </div><!-- /hidden md:block -->

            <!-- ════════════════════════════════════════════════
                 DIALOG de edición inline
            ═══════════════════════════════════════════════════ -->
            <Dialog
                v-model:visible="dialogVisible"
                header="Editar usuario"
                :modal="true"
                :draggable="false"
                :style="{ width: '480px' }"
                :breakpoints="{ '640px': '95vw' }"
            >
                <form @submit.prevent="guardarUsuario" class="space-y-5 pt-1">

                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Nombre completo
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="off"
                            class="block w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition"
                        />
                        <p v-if="erroresForm.name" class="mt-1 text-xs text-red-500">{{ erroresForm.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Correo electrónico
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="off"
                            class="block w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition"
                        />
                        <p v-if="erroresForm.email" class="mt-1 text-xs text-red-500">{{ erroresForm.email }}</p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Rol
                        </label>
                        <Select
                            v-model="form.rol"
                            :options="roles"
                            placeholder="Selecciona un rol"
                            class="w-full"
                        />
                        <p v-if="erroresForm.rol" class="mt-1 text-xs text-red-500">{{ erroresForm.rol }}</p>
                    </div>

                    <!-- Nueva contraseña (opcional) -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Nueva contraseña <span class="text-xs text-zinc-400 font-normal">(opcional)</span>
                        </label>
                        <PasswordInput
                            id="edit_inline_password"
                            v-model="form.password"
                            placeholder="•••••••• (dejar en blanco para no modificar)"
                            autocomplete="new-password"
                        />
                        <p v-if="erroresForm.password" class="mt-1 text-xs text-red-500">{{ erroresForm.password }}</p>
                    </div>

                    <!-- Acciones del dialog -->
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <Button
                            label="Cancelar"
                            severity="secondary"
                            type="button"
                            @click="dialogVisible = false"
                        />
                        <Button
                            label="Guardar cambios"
                            type="submit"
                            :loading="guardando"
                            icon="pi pi-check"
                        />
                    </div>
                </form>
            </Dialog>

        </div>
    </AuthenticatedLayout>
</template>
