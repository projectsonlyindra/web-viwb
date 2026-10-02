<script setup>
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatStatus } from '@/lib/utils';

const props = defineProps({
    periode: String,
    is_warga: Boolean,
    jumlah_warga_aktif: Number,
    jumlah_tagihan_bulan_ini: Number,
    total_tagihan_bulan_ini: Number,
    total_terbayar_bulan_ini: Number,
    pengeluaran_menunggu_approval: Number,
    alert_tunggakan: {
        type: Array,
        default: () => [],
    },
    tagihan_aktif_count: Number,
    tagihan_aktif_total: Number,
    tagihan_aktif: {
        type: Array,
        default: () => [],
    },
    pembayaran_terakhir: Object,
});

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka ?? 0);
}

function formatTanggal(tanggal) {
    return tanggal ? tanggal.slice(0, 10) : '-';
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
                <!-- Tampilan Khusus Warga -->
                <template v-if="is_warga">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Tagihan Belum Lunas</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ tagihan_aktif_count ?? 0 }}</div>
                            <div class="mt-1 text-xs text-gray-500">Total: {{ formatRupiah(tagihan_aktif_total) }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Pembayaran Terakhir</div>
                            <div class="mt-1 text-base font-semibold text-gray-900">
                                {{ pembayaran_terakhir ? formatRupiah(pembayaran_terakhir.total_dibayar) : 'Belum Ada' }}
                            </div>
                            <div v-if="pembayaran_terakhir" class="mt-1 text-xs text-gray-500">
                                Status:
                                <span class="font-medium" :class="{
                                    'text-green-600': pembayaran_terakhir.status === 'DIKONFIRMASI',
                                    'text-yellow-600': pembayaran_terakhir.status === 'MENUNGGU_KONFIRMASI',
                                    'text-red-600': pembayaran_terakhir.status === 'DITOLAK'
                                }">
                                    {{ formatStatus(pembayaran_terakhir.status) }}
                                </span>
                                ({{ formatTanggal(pembayaran_terakhir.created_at) }})
                            </div>
                        </div>
                        <div class="flex flex-col justify-center rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Aksi Cepat</div>
                            <div class="mt-2">
                                <Link
                                    :href="route('pembayaran.index')"
                                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                >
                                    Bayar Tagihan Sekarang
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b p-4">
                            <h3 class="font-medium text-gray-900">Daftar Tagihan Belum Lunas</h3>
                            <p class="text-xs text-gray-500">Tagihan aktif yang membutuhkan pembayaran atau pelunasan.</p>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Periode</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Jenis Layanan</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Pokok</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Denda</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="t in tagihan_aktif" :key="t.id">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ t.periode }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ t.jenis }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ formatRupiah(t.nominal) }}</td>
                                    <td class="px-4 py-3 text-right text-red-600">{{ t.denda > 0 ? formatRupiah(t.denda) : '-' }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ formatRupiah(Number(t.nominal) + Number(t.denda ?? 0)) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                                            {{ formatStatus(t.status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="tagihan_aktif.length === 0">
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Semua tagihan telah lunas. Terima kasih atas partisipasi Anda!
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <!-- Tampilan Admin & Pengurus -->
                <template v-else>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Warga Aktif</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-800">{{ jumlah_warga_aktif }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Tagihan Bulan Ini</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-800">{{ jumlah_tagihan_bulan_ini }}</div>
                            <div class="text-xs text-gray-500">{{ formatRupiah(total_tagihan_bulan_ini) }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Terbayar Bulan Ini</div>
                            <div class="mt-1 text-2xl font-semibold text-green-600">{{ formatRupiah(total_terbayar_bulan_ini) }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Pengeluaran Menunggu Approval</div>
                            <div class="mt-1 text-2xl font-semibold text-amber-600">{{ pengeluaran_menunggu_approval }}</div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b p-4">
                            <h3 class="font-medium text-gray-800">Alert Tunggakan</h3>
                            <p class="text-xs text-gray-500">Warga dengan tunggakan melebihi batas alert per jenis layanan</p>
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
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        Tidak ada alert tunggakan saat ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
