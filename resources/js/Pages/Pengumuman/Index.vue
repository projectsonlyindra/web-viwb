<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';

defineProps({
    pengumuman: Array,
    canCreate: Boolean,
});

const showForm = ref(false);

const form = useForm({
    judul: '',
    isi: '',
    target_blok: '',
});

function submit() {
    form.post(route('pengumuman.store'), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}
</script>

<template>
    <Head title="Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pengumuman
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="$page.props.flash?.success"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-700"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="canCreate" class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-800">Buat Pengumuman</h3>
                        <PrimaryButton @click="showForm = !showForm">
                            {{ showForm ? 'Tutup Form' : '+ Buat Pengumuman' }}
                        </PrimaryButton>
                    </div>

                    <form v-if="showForm" @submit.prevent="submit" class="mt-4 space-y-4 border-t pt-4">
                        <div>
                            <InputLabel for="judul" value="Judul" />
                            <TextInput id="judul" v-model="form.judul" class="mt-1 block w-full" />
                            <InputError :message="form.errors.judul" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="isi" value="Isi" />
                            <textarea
                                id="isi"
                                v-model="form.isi"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            />
                            <InputError :message="form.errors.isi" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="target_blok" value="Target Blok (kosongkan untuk semua warga)" />
                            <TextInput id="target_blok" v-model="form.target_blok" placeholder="A" class="mt-1 block w-24 uppercase" />
                            <InputError :message="form.errors.target_blok" class="mt-1" />
                        </div>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Mengantrekan...' : 'Kirim & Antrekan Broadcast' }}
                        </PrimaryButton>
                    </form>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="p in pengumuman"
                        :key="p.id"
                        class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-800">{{ p.judul }}</h3>
                            <span class="text-xs text-gray-500">
                                {{ p.target_blok ? `Blok ${p.target_blok}` : 'Semua warga' }}
                            </span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ p.isi }}</p>
                    </div>
                    <div v-if="pengumuman.length === 0" class="bg-white p-10 text-center text-sm text-gray-500 shadow-sm sm:rounded-lg">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <p class="font-medium text-gray-700">Belum ada pengumuman</p>
                            <p class="text-xs text-gray-400 mt-1">Pengumuman paguyuban dan RT akan ditampilkan di sini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
