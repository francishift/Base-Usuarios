<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-slate-900 dark:text-white">
                Eliminar cuenta
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Una vez eliminada tu cuenta, todos sus datos y recursos se borrarán permanentemente.
                Antes de eliminarla, descarga cualquier información que desees conservar.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Eliminar cuenta</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 dark:text-white">
                    ¿Estás seguro de que quieres eliminar tu cuenta?
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Esta acción es irreversible. Se eliminarán permanentemente todos tus datos.
                    Introduce tu contraseña para confirmar.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Contraseña"
                        class="sr-only"
                    />
                    <PasswordInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        autocomplete="current-password"
                        placeholder="Contraseña"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Sí, eliminar mi cuenta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
