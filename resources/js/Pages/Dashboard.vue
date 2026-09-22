<script setup>
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    periode: String,
    jumlah_warga_aktif: Number,
    jumlah_tagihan_bulan_ini: Number,
    total_tagihan_bulan_ini: Number,
    total_terbayar_bulan_ini: Number,
    pengeluaran_menunggu_approval: Number,
    alert_tunggakan: Array,
});

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka ?? 0);
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard ({{ periode }})
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Warga Aktif</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-800">{{ jumlah_warga_aktif }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Tagihan Bulan Ini</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-800">{{ jumlah_tagihan_bulan_ini }}</div>
                        <div class="text-xs text-gray-400">{{ formatRupiah(total_tagihan_bulan_ini) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Terbayar Bulan Ini</div>
                        <div class="mt-1 text-2xl font-semibold text-green-600">{{ formatRupiah(total_terbayar_bulan_ini) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Pengeluaran Menunggu Approval</div>
                        <div class="mt-1 text-2xl font-semibold text-amber-600">{{ pengeluaran_menunggu_approval }}</div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b p-4">
                        <h3 class="font-medium text-gray-800">Alert Tunggakan</h3>
                        <p class="text-xs text-gray-400">Warga dengan tunggakan melebihi batas alert per jenis layanan</p>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Jenis</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Periode Tertunggak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(a, idx) in alert_tunggakan" :key="idx">
                                <td class="px-4 py-3">{{ a.unit_id }}</td>
                                <td class="px-4 py-3">{{ a.warga }}</td>
                                <td class="px-4 py-3">{{ a.jenis }}</td>
                                <td class="px-4 py-3 text-right text-red-600">{{ a.jumlah_periode }}</td>
                            </tr>
                            <tr v-if="alert_tunggakan.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                    Tidak ada alert tunggakan saat ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
