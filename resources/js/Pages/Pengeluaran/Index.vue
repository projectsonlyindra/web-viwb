<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import DangerButton from '@/Shared/Components/DangerButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';

const props = defineProps({
    pengeluaran: Array,
    filters: Object,
    canCreate: Boolean,
    userId: Number,
});

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function formatTanggal(tanggal) {
    return tanggal ? tanggal.slice(0, 10) : '-';
}

function dibuatOlehSaya(p) {
    return Number(p.dibuat_oleh_id) === Number(props.userId);
}

const showForm = ref(false);

const form = useForm({
    kategori: '',
    keterangan: '',
    nominal: '',
    tanggal: new Date().toISOString().slice(0, 10),
    bukti: null,
});

function submit() {
    form.post(route('pengeluaran.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function ajukan(id) {
    router.post(route('pengeluaran.submit', id));
}

function approve(id) {
    router.post(route('pengeluaran.approve', id));
}

const menolakId = ref(null);
const catatanTolak = ref('');

function bukaFormTolak(id) {
    menolakId.value = id;
    catatanTolak.value = '';
}

function kirimTolak(id) {
    if (!catatanTolak.value) return;
    router.post(route('pengeluaran.reject', id), { catatan_review: catatanTolak.value }, {
        onSuccess: () => (menolakId.value = null),
    });
}
</script>

<template>
    <Head title="Pengeluaran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pengeluaran
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
                        <h3 class="font-medium text-gray-800">Entri Pengeluaran</h3>
                        <PrimaryButton @click="showForm = !showForm">
                            {{ showForm ? 'Tutup Form' : '+ Tambah' }}
                        </PrimaryButton>
                    </div>

                    <form v-if="showForm" @submit.prevent="submit" class="mt-4 grid grid-cols-1 gap-4 border-t pt-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="kategori" value="Kategori" />
                            <TextInput id="kategori" v-model="form.kategori" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="nominal" value="Nominal" />
                            <TextInput id="nominal" v-model="form.nominal" type="number" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="tanggal" value="Tanggal" />
                            <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="bukti" value="Bukti (opsional)" />
                            <input
                                id="bukti"
                                type="file"
                                accept="image/*"
                                class="mt-1 block text-sm"
                                @input="form.bukti = $event.target.files[0]"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="keterangan" value="Keterangan" />
                            <textarea
                                id="keterangan"
                                v-model="form.keterangan"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Kategori</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Dibuat Oleh</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="p in pengeluaran" :key="p.id">
                                <tr>
                                    <td class="px-4 py-3">{{ p.kategori }}</td>
                                    <td class="px-4 py-3">{{ formatTanggal(p.tanggal) }}</td>
                                    <td class="px-4 py-3 text-right">{{ formatRupiah(p.nominal) }}</td>
                                    <td class="px-4 py-3">{{ p.dibuat_oleh?.name }}</td>
                                    <td class="px-4 py-3">{{ p.status }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <SecondaryButton
                                                v-if="p.status === 'DRAFT' && dibuatOlehSaya(p)"
                                                @click="ajukan(p.id)"
                                            >
                                                Ajukan
                                            </SecondaryButton>
                                            <template v-if="p.status === 'MENUNGGU_APPROVAL' && !dibuatOlehSaya(p)">
                                                <SecondaryButton @click="approve(p.id)">Approve</SecondaryButton>
                                                <DangerButton @click="bukaFormTolak(p.id)">Tolak</DangerButton>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="menolakId === p.id" class="bg-red-50">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <input
                                                v-model="catatanTolak"
                                                type="text"
                                                placeholder="Alasan penolakan..."
                                                class="block flex-1 rounded-md border-gray-300 text-sm shadow-sm"
                                            />
                                            <DangerButton @click="kirimTolak(p.id)">Kirim</DangerButton>
                                            <SecondaryButton @click="menolakId = null">Batal</SecondaryButton>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="pengeluaran.length === 0">
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada pengeluaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
