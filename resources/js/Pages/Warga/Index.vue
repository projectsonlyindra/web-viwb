<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Shared/Components/InputError.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import DangerButton from '@/Shared/Components/DangerButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';
import Modal from '@/Shared/Components/Modal.vue';
import { formatStatus } from '@/lib/utils';

const props = defineProps({
    warga: Array,
    filters: Object,
});

const page = usePage();
const isSuperAdmin = computed(() => page.props.auth?.user?.role === 'SUPERADMIN');

const blok = ref(props.filters.blok ?? '');
const status = ref(props.filters.status ?? '');
const deletingId = ref(null);

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

const editingWarga = ref(null);
const editForm = useForm({
    nik: '',
    nama: '',
    no_wa: '',
    unit_id: '',
    jenis_kendaraan: 'TIDAK_ADA',
    status_warga: 'AKTIF',
    ikut_hippam: false,
    ikut_kebersihan: false,
});

function bukaEdit(w) {
    editingWarga.value = w;
    editForm.nik = w.nik ?? '';
    editForm.nama = w.nama ?? '';
    editForm.no_wa = w.no_wa ?? '';
    editForm.unit_id = w.unit_id ?? '';
    editForm.jenis_kendaraan = w.jenis_kendaraan ?? 'TIDAK_ADA';
    editForm.status_warga = w.status_warga ?? 'AKTIF';
    editForm.ikut_hippam = Boolean(w.ikut_hippam);
    editForm.ikut_kebersihan = Boolean(w.ikut_kebersihan);
    editForm.clearErrors();
}

function simpanEdit() {
    if (!editingWarga.value) return;
    editForm.put(route('warga.update', editingWarga.value.id), {
        onSuccess: () => {
            editingWarga.value = null;
        },
    });
}

function hapus(id) {
    if (confirm('Hapus data warga ini?')) {
        deletingId.value = id;
        router.delete(route('warga.destroy', id), {
            onFinish: () => {
                deletingId.value = null;
            },
        });
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

                        <div v-if="isSuperAdmin" class="ms-auto">
                            <PrimaryButton @click="showForm = !showForm">
                                {{ showForm ? 'Tutup Form' : '+ Tambah Warga' }}
                            </PrimaryButton>
                        </div>
                    </div>

                    <form
                        v-if="showForm && isSuperAdmin"
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
                            <InputError :message="form.errors.jenis_kendaraan" class="mt-1" />
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
                            <InputError :message="form.errors.status_warga" class="mt-1" />
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
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Daftar Data Warga">
                        <caption class="sr-only">Tabel Data Warga Perumahan</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">No. WA</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Kendaraan</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Layanan</th>
                                <th v-if="isSuperAdmin" scope="col" class="px-4 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="w in warga" :key="w.id">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ w.unit_id }}</td>
                                <td class="px-4 py-3 text-gray-900">{{ w.nama }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ w.no_wa }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ w.jenis_kendaraan }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                             'bg-green-100 text-green-800': w.status_warga === 'AKTIF',
                                            'bg-yellow-100 text-yellow-800': w.status_warga === 'KONTRAK',
                                            'bg-gray-100 text-gray-700': w.status_warga === 'PINDAH',
                                        }"
                                    >
                                        {{ formatStatus(w.status_warga) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <span v-if="w.ikut_hippam">HIPPAM</span>
                                    <span v-if="w.ikut_hippam && w.ikut_kebersihan">, </span>
                                    <span v-if="w.ikut_kebersihan">Kebersihan</span>
                                    <span v-if="!w.ikut_hippam && !w.ikut_kebersihan">-</span>
                                </td>
                                <td v-if="isSuperAdmin" class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <SecondaryButton @click="bukaEdit(w)">Edit</SecondaryButton>
                                        <DangerButton :disabled="deletingId === w.id" @click="hapus(w.id)">
                                            {{ deletingId === w.id ? 'Menghapus...' : 'Hapus' }}
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="warga.length === 0">
                                <td :colspan="isSuperAdmin ? 7 : 6" class="px-4 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="font-medium text-gray-700">Belum ada data warga</p>
                                        <p class="text-xs text-gray-400 mt-1">Data warga yang terdaftar akan muncul di tabel ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="editingWarga !== null" @close="editingWarga = null" max-width="2xl">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Edit Data Warga: {{ editingWarga?.unit_id }}
                </h3>
                <form @submit.prevent="simpanEdit" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="edit_nik" value="NIK" />
                        <TextInput id="edit_nik" v-model="editForm.nik" class="mt-1 block w-full" maxlength="16" />
                        <InputError :message="editForm.errors.nik" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_nama" value="Nama" />
                        <TextInput id="edit_nama" v-model="editForm.nama" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.nama" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_no_wa" value="No. WA" />
                        <TextInput id="edit_no_wa" v-model="editForm.no_wa" class="mt-1 block w-full" />
                        <InputError :message="editForm.errors.no_wa" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_unit_id" value="Unit (contoh: A01)" />
                        <TextInput id="edit_unit_id" v-model="editForm.unit_id" class="mt-1 block w-full uppercase" />
                        <InputError :message="editForm.errors.unit_id" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_jenis_kendaraan" value="Jenis Kendaraan" />
                        <select
                            id="edit_jenis_kendaraan"
                            v-model="editForm.jenis_kendaraan"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                        >
                            <option value="TIDAK_ADA">Tidak Ada</option>
                            <option value="MOTOR">Motor</option>
                            <option value="MOBIL">Mobil</option>
                        </select>
                        <InputError :message="editForm.errors.jenis_kendaraan" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_status_warga" value="Status Warga" />
                        <select
                            id="edit_status_warga"
                            v-model="editForm.status_warga"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                        >
                            <option value="AKTIF">AKTIF</option>
                            <option value="PINDAH">PINDAH</option>
                            <option value="KONTRAK">KONTRAK</option>
                        </select>
                        <InputError :message="editForm.errors.status_warga" class="mt-1" />
                    </div>
                    <div class="flex items-center gap-4 sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="editForm.ikut_hippam" />
                            Ikut HIPPAM
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="editForm.ikut_kebersihan" />
                            Ikut Kebersihan
                        </label>
                    </div>
                    <div class="mt-4 flex justify-end gap-2 sm:col-span-2">
                        <SecondaryButton @click="editingWarga = null">Batal</SecondaryButton>
                        <PrimaryButton :disabled="editForm.processing">
                            {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
