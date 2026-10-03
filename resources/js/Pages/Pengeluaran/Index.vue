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
import Pagination from '@/Shared/Components/Pagination.vue';
import { formatStatus } from '@/lib/utils';

const props = defineProps({
    pengeluaran: [Object, Array],
    filters: Object,
    canCreate: Boolean,
    userId: Number,
});

const items = computed(() => Array.isArray(props.pengeluaran) ? props.pengeluaran : (props.pengeluaran?.data ?? []));

const page = usePage();

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function formatTanggal(tanggal) {
    return tanggal ? tanggal.slice(0, 10) : '-';
}

function dibuatOlehSaya(p) {
    return Number(p.dibuat_oleh_id) === Number(props.userId);
}

function bisaApprove(p) {
    const userRole = page.props.auth?.user?.role;
    return ['SUPERADMIN', 'KETUA_RT'].includes(userRole) && !dibuatOlehSaya(p);
}

const showForm = ref(false);
const processingActionId = ref(null);

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
    if (!confirm('Ajukan pengeluaran ini untuk disetujui Ketua RT?')) return;
    processingActionId.value = id;
    router.post(route('pengeluaran.submit', id), {}, {
        onFinish: () => (processingActionId.value = null),
    });
}

function approve(id) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengeluaran ini?')) return;
    processingActionId.value = id;
    router.post(route('pengeluaran.approve', id), {}, {
        onFinish: () => (processingActionId.value = null),
    });
}

const menolakId = ref(null);
const catatanTolak = ref('');

function bukaFormTolak(id) {
    menolakId.value = id;
    catatanTolak.value = '';
}

function kirimTolak(id) {
    if (!catatanTolak.value) return;
    if (!confirm('Apakah Anda yakin ingin menolak pengeluaran ini?')) return;
    processingActionId.value = id;
    router.post(route('pengeluaran.reject', id), { catatan_review: catatanTolak.value }, {
        onSuccess: () => (menolakId.value = null),
        onFinish: () => (processingActionId.value = null),
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
                            <InputError :message="form.errors.kategori" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="nominal" value="Nominal" />
                            <TextInput id="nominal" v-model="form.nominal" type="number" class="mt-1 block w-full" />
                            <InputError :message="form.errors.nominal" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="tanggal" value="Tanggal" />
                            <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-1 block w-full" />
                            <InputError :message="form.errors.tanggal" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="bukti" value="Bukti (opsional)" />
                            <input
                                id="bukti"
                                type="file"
                                accept="image/*"
                                class="mt-1 block text-sm text-gray-600"
                                @input="form.bukti = $event.target.files[0]"
                            />
                            <InputError :message="form.errors.bukti" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="keterangan" value="Keterangan" />
                            <textarea
                                id="keterangan"
                                v-model="form.keterangan"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            />
                            <InputError :message="form.errors.keterangan" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Daftar Pengeluaran Kas">
                        <caption class="sr-only">Tabel Pengeluaran Kas Paguyuban</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Kategori</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Tanggal</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Dibuat Oleh</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="p in items" :key="p.id">
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ p.kategori }}</div>
                                        <div v-if="p.keterangan" class="text-xs text-gray-500">{{ p.keterangan }}</div>
                                        <div v-if="p.bukti_url" class="mt-1">
                                            <a :href="p.bukti_url" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                                Lihat Bukti
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ formatTanggal(p.tanggal) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatRupiah(p.nominal) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ p.dibuat_oleh?.name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': p.status === 'MENUNGGU_APPROVAL',
                                                'bg-green-100 text-green-800': p.status === 'DISETUJUI',
                                                'bg-red-100 text-red-800': p.status === 'DITOLAK',
                                                'bg-gray-100 text-gray-700': p.status === 'DRAFT'
                                            }">
                                            {{ formatStatus(p.status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <SecondaryButton
                                                v-if="p.status === 'DRAFT' && dibuatOlehSaya(p)"
                                                :disabled="processingActionId === p.id"
                                                @click="ajukan(p.id)"
                                            >
                                                {{ processingActionId === p.id ? 'Memproses...' : 'Ajukan' }}
                                            </SecondaryButton>
                                            <template v-if="p.status === 'MENUNGGU_APPROVAL' && bisaApprove(p)">
                                                <SecondaryButton
                                                    :disabled="processingActionId === p.id"
                                                    @click="approve(p.id)"
                                                >
                                                    {{ processingActionId === p.id ? 'Memproses...' : 'Approve' }}
                                                </SecondaryButton>
                                                <DangerButton
                                                    :disabled="processingActionId === p.id"
                                                    @click="bukaFormTolak(p.id)"
                                                >
                                                    Tolak
                                                </DangerButton>
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
                                            <DangerButton :disabled="processingActionId === p.id" @click="kirimTolak(p.id)">
                                                {{ processingActionId === p.id ? 'Mengirim...' : 'Kirim' }}
                                            </DangerButton>
                                            <SecondaryButton @click="menolakId = null">Batal</SecondaryButton>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="items.length === 0">
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="font-medium text-gray-700">Belum ada pengeluaran</p>
                                        <p class="text-xs text-gray-400 mt-1">Pengeluaran kas yang dicatat akan muncul di tabel ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :links="pengeluaran.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
