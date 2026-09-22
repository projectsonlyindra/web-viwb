<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import DangerButton from '@/Shared/Components/DangerButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';

const props = defineProps({
    pembayaran: Array,
    filters: Object,
    tagihanBelumLunas: Array,
    canConfirm: Boolean,
});

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
        .reduce((sum, t) => sum + t.nominal, 0),
);

function submit() {
    form.post(route('pembayaran.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
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
    router.post(route('pembayaran.reject', id), { catatan: catatanTolak.value }, {
        onSuccess: () => (menolakId.value = null),
    });
}

function confirmPembayaran(id) {
    router.post(route('pembayaran.confirm', id));
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
                                class="flex items-center justify-between rounded-md border border-gray-200 p-3 text-sm"
                            >
                                <span class="flex items-center gap-3">
                                    <input type="checkbox" :value="t.id" v-model="form.tagihan_ids" />
                                    {{ t.jenis }} (periode {{ t.periode }})
                                </span>
                                <span>{{ formatRupiah(t.nominal) }}</span>
                            </label>
                        </div>

                        <div class="text-sm font-medium">
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
                        </div>

                        <div>
                            <InputLabel for="catatan" value="Catatan (opsional)" />
                            <textarea
                                id="catatan"
                                v-model="form.catatan"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"
                            />
                        </div>

                        <PrimaryButton :disabled="form.processing || form.tagihan_ids.length === 0">
                            Ajukan Pembayaran
                        </PrimaryButton>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Warga</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Tagihan</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in pembayaran" :key="p.id">
                                <td class="px-4 py-3">{{ p.warga.unit_id }} - {{ p.warga.nama }}</td>
                                <td class="px-4 py-3">
                                    <div v-for="item in p.item" :key="item.id">
                                        {{ item.tagihan.jenis }} ({{ item.tagihan.periode }})
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">{{ formatRupiah(p.total_dibayar) }}</td>
                                <td class="px-4 py-3">{{ p.status }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div v-if="canConfirm && p.status === 'MENUNGGU_KONFIRMASI'" class="flex justify-end gap-2">
                                        <SecondaryButton @click="confirmPembayaran(p.id)">Konfirmasi</SecondaryButton>
                                        <DangerButton @click="bukaFormTolak(p.id)">Tolak</DangerButton>
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
                                        <DangerButton @click="kirimTolak(menolakId)">Kirim</DangerButton>
                                        <SecondaryButton @click="menolakId = null">Batal</SecondaryButton>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pembayaran.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada pembayaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
