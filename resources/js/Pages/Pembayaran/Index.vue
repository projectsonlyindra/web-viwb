<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Shared/Components/InputError.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import DangerButton from '@/Shared/Components/DangerButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import Pagination from '@/Shared/Components/Pagination.vue';
import { formatStatus } from '@/lib/utils';

const props = defineProps({
    pembayaran: [Object, Array],
    filters: Object,
    tagihanBelumLunas: Array,
    canConfirm: Boolean,
});

const items = computed(() => Array.isArray(props.pembayaran) ? props.pembayaran : (props.pembayaran?.data ?? []));

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

const form = useForm({
    tagihan_ids: [],
    bukti: null,
    catatan: '',
});

const totalDipilih = computed(() =>
    props.tagihanBelumLunas
        .filter((t) => form.tagihan_ids.includes(t.id))
        .reduce((sum, t) => sum + t.nominal + (t.denda || 0), 0),
);

function submit() {
    form.post(route('pembayaran.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
}

const menolakId = ref(null);
const catatanTolak = ref('');
const processingActionId = ref(null);

function bukaFormTolak(id) {
    menolakId.value = id;
    catatanTolak.value = '';
}

function kirimTolak(id) {
    if (!catatanTolak.value) return;
    if (!confirm('Apakah Anda yakin ingin menolak pembayaran ini?')) return;
    processingActionId.value = id;
    router.post(route('pembayaran.reject', id), { catatan: catatanTolak.value }, {
        onSuccess: () => (menolakId.value = null),
        onFinish: () => (processingActionId.value = null),
    });
}

function confirmPembayaran(id) {
    if (!confirm('Apakah Anda yakin ingin mengonfirmasi dan melunasi pembayaran ini?')) return;
    processingActionId.value = id;
    router.post(route('pembayaran.confirm', id), {}, {
        onFinish: () => (processingActionId.value = null),
    });
}
</script>

<template>
    <Head title="Pembayaran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pembayaran
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

                <div v-if="tagihanBelumLunas.length > 0" class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 font-medium text-gray-800">Bayar Tagihan</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <label
                                v-for="t in tagihanBelumLunas"
                                :key="t.id"
                                class="flex items-center justify-between rounded-md border border-gray-200 p-3 text-sm hover:bg-gray-50 cursor-pointer"
                            >
                                <span class="flex items-center gap-3">
                                    <input type="checkbox" :value="t.id" v-model="form.tagihan_ids" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span>
                                        <span class="font-medium text-gray-800">{{ t.jenis }}</span>
                                        <span class="ml-1 text-xs text-gray-500">(periode {{ t.periode }})</span>
                                    </span>
                                </span>
                                <div class="text-right">
                                    <span class="font-medium text-gray-800">{{ formatRupiah(t.nominal + (t.denda || 0)) }}</span>
                                    <span v-if="t.denda > 0" class="block text-xs text-red-600">
                                        + Denda: {{ formatRupiah(t.denda) }}
                                    </span>
                                </div>
                            </label>
                            <InputError :message="form.errors.tagihan_ids" class="mt-1" />
                        </div>

                        <div class="text-sm font-medium text-gray-800">
                            Total dipilih: {{ formatRupiah(totalDipilih) }}
                        </div>

                        <div>
                            <InputLabel for="bukti" value="Bukti Bayar (opsional)" />
                            <input
                                id="bukti"
                                type="file"
                                accept="image/*"
                                class="mt-1 block text-sm"
                                @input="form.bukti = $event.target.files[0]"
                            />
                            <InputError :message="form.errors.bukti" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="catatan" value="Catatan (opsional)" />
                            <textarea
                                id="catatan"
                                v-model="form.catatan"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            />
                            <InputError :message="form.errors.catatan" class="mt-1" />
                        </div>

                        <PrimaryButton :disabled="form.processing || form.tagihan_ids.length === 0">
                            {{ form.processing ? 'Memproses...' : 'Ajukan Pembayaran' }}
                        </PrimaryButton>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Daftar Riwayat Pembayaran">
                        <caption class="sr-only">Tabel Riwayat Pembayaran Tagihan</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Warga</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Tagihan</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in items" :key="p.id">
                                <td class="px-4 py-3">
                                    <div>{{ p.warga?.unit_id ?? '-' }} - {{ p.warga?.nama ?? 'Warga Nonaktif' }}</div>
                                    <a
                                        v-if="p.bukti_url"
                                        :href="`/storage/${p.bukti_url}`"
                                        target="_blank"
                                        class="mt-1 inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800 underline"
                                    >
                                        Lihat Bukti Transfer
                                    </a>
                                    <div v-if="p.catatan" class="mt-1 text-xs text-gray-500 italic">
                                        Catatan Warga: "{{ p.catatan }}"
                                    </div>
                                    <div v-if="p.catatan_review" class="mt-1 text-xs text-red-600 bg-red-50 p-1.5 rounded">
                                        Catatan Review Pengurus: "{{ p.catatan_review }}"
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-for="item in p.item" :key="item.id">
                                        {{ item.tagihan?.jenis ?? '-' }} ({{ item.tagihan?.periode ?? '-' }})
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatRupiah(p.total_dibayar) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                             'bg-yellow-100 text-yellow-800': p.status === 'MENUNGGU_KONFIRMASI',
                                            'bg-green-100 text-green-800': p.status === 'DIKONFIRMASI',
                                            'bg-red-100 text-red-800': p.status === 'DITOLAK',
                                        }"
                                    >
                                        {{ formatStatus(p.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div v-if="canConfirm && p.status === 'MENUNGGU_KONFIRMASI'" class="flex justify-end gap-2">
                                        <SecondaryButton
                                            :disabled="processingActionId === p.id"
                                            @click="confirmPembayaran(p.id)"
                                        >
                                            {{ processingActionId === p.id ? 'Memproses...' : 'Konfirmasi' }}
                                        </SecondaryButton>
                                        <DangerButton
                                            :disabled="processingActionId === p.id"
                                            @click="bukaFormTolak(p.id)"
                                        >
                                            Tolak
                                        </DangerButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="menolakId" class="bg-red-50">
                                <td colspan="5" class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <input
                                            v-model="catatanTolak"
                                            type="text"
                                            placeholder="Alasan penolakan..."
                                            class="block flex-1 rounded-md border-gray-300 text-sm shadow-sm"
                                        />
                                        <DangerButton
                                            :disabled="processingActionId === menolakId"
                                            @click="kirimTolak(menolakId)"
                                        >
                                            {{ processingActionId === menolakId ? 'Mengirim...' : 'Kirim' }}
                                        </DangerButton>
                                        <SecondaryButton @click="menolakId = null">Batal</SecondaryButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="font-medium text-gray-700">Belum ada data pembayaran</p>
                                        <p class="text-xs text-gray-400 mt-1">Pembayaran yang diajukan akan tercatat di tabel ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :links="pembayaran.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
