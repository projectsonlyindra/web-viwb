<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Shared/Components/InputError.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import DangerButton from '@/Shared/Components/DangerButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';

const props = defineProps({
    warga: Array,
    filters: Object,
});

const blok = ref(props.filters.blok ?? '');
const status = ref(props.filters.status ?? '');

function terapkanFilter() {
    router.get(
        route('warga.index'),
        { blok: blok.value || undefined, status: status.value || undefined },
        { preserveState: true, replace: true },
    );
}

const showForm = ref(false);

const form = useForm({
    nik: '',
    nama: '',
    no_wa: '',
    unit_id: '',
    jenis_kendaraan: 'TIDAK_ADA',
    status_warga: 'AKTIF',
    ikut_hippam: false,
    ikut_kebersihan: false,
});

function submit() {
    form.post(route('warga.store'), {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function hapus(id) {
    if (confirm('Hapus warga ini?')) {
        router.delete(route('warga.destroy', id));
    }
}
</script>

<template>
    <Head title="Warga" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Data Warga
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

                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <InputLabel for="blok" value="Filter Blok" />
                            <TextInput
                                id="blok"
                                v-model="blok"
                                placeholder="A"
                                class="mt-1 block w-24"
                                @keyup.enter="terapkanFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="status" value="Filter Status" />
                            <select
                                id="status"
                                v-model="status"
                                class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm"
                                @change="terapkanFilter"
                            >
                                <option value="">Semua</option>
                                <option value="AKTIF">AKTIF</option>
                                <option value="PINDAH">PINDAH</option>
                                <option value="KONTRAK">KONTRAK</option>
                            </select>
                        </div>
                        <SecondaryButton @click="terapkanFilter">Terapkan</SecondaryButton>

                        <div class="ms-auto">
                            <PrimaryButton @click="showForm = !showForm">
                                {{ showForm ? 'Tutup Form' : '+ Tambah Warga' }}
                            </PrimaryButton>
                        </div>
                    </div>

                    <form
                        v-if="showForm"
                        @submit.prevent="submit"
                        class="mt-6 grid grid-cols-1 gap-4 border-t pt-6 sm:grid-cols-3"
                    >
                        <div>
                            <InputLabel for="nik" value="NIK" />
                            <TextInput id="nik" v-model="form.nik" class="mt-1 block w-full" maxlength="16" />
                            <InputError :message="form.errors.nik" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="nama" value="Nama" />
                            <TextInput id="nama" v-model="form.nama" class="mt-1 block w-full" />
                            <InputError :message="form.errors.nama" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="no_wa" value="No. WA" />
                            <TextInput id="no_wa" v-model="form.no_wa" class="mt-1 block w-full" />
                            <InputError :message="form.errors.no_wa" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="unit_id" value="Unit (contoh: A01)" />
                            <TextInput id="unit_id" v-model="form.unit_id" class="mt-1 block w-full uppercase" />
                            <InputError :message="form.errors.unit_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="jenis_kendaraan" value="Jenis Kendaraan" />
                            <select
                                id="jenis_kendaraan"
                                v-model="form.jenis_kendaraan"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            >
                                <option value="TIDAK_ADA">Tidak Ada</option>
                                <option value="MOTOR">Motor</option>
                                <option value="MOBIL">Mobil</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="status_warga" value="Status Warga" />
                            <select
                                id="status_warga"
                                v-model="form.status_warga"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            >
                                <option value="AKTIF">AKTIF</option>
                                <option value="PINDAH">PINDAH</option>
                                <option value="KONTRAK">KONTRAK</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4 sm:col-span-3">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.ikut_hippam" />
                                Ikut HIPPAM
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" v-model="form.ikut_kebersihan" />
                                Ikut Kebersihan
                            </label>
                        </div>
                        <div class="sm:col-span-3">
                            <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">No. WA</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Kendaraan</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Layanan</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="w in warga" :key="w.id">
                                <td class="px-4 py-3 font-medium">{{ w.unit_id }}</td>
                                <td class="px-4 py-3">{{ w.nama }}</td>
                                <td class="px-4 py-3">{{ w.no_wa }}</td>
                                <td class="px-4 py-3">{{ w.jenis_kendaraan }}</td>
                                <td class="px-4 py-3">{{ w.status_warga }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="w.ikut_hippam">HIPPAM</span>
                                    <span v-if="w.ikut_hippam && w.ikut_kebersihan">, </span>
                                    <span v-if="w.ikut_kebersihan">Kebersihan</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DangerButton @click="hapus(w.id)">Hapus</DangerButton>
                                </td>
                            </tr>
                            <tr v-if="warga.length === 0">
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada data warga.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
