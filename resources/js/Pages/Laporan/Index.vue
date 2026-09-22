<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';

const props = defineProps({
    periode: String,
    tagihan: Array,
    pendapatan: Array,
    pengeluaran: Array,
    totalTagihan: Number,
    totalLunas: Number,
    totalPendapatan: Number,
    totalPengeluaran: Number,
    saldo: Number,
});

const periodeInput = ref(props.periode);

function terapkan() {
    router.get(route('laporan.index'), { periode: periodeInput.value }, { preserveState: true });
}

function cetak() {
    window.print();
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka ?? 0);
}

function formatTanggal(tanggal) {
    return tanggal ? tanggal.slice(0, 10) : '-';
}
</script>

<template>
    <Head title="Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Laporan Bulanan
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg print:hidden">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <InputLabel for="periode" value="Periode" />
                            <TextInput id="periode" v-model="periodeInput" type="month" class="mt-1 block" />
                        </div>
                        <SecondaryButton @click="terapkan">Tampilkan</SecondaryButton>
                        <PrimaryButton @click="cetak">Cetak</PrimaryButton>
                        <a
                            :href="route('laporan.export', { periode })"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Export CSV
                        </a>
                    </div>
                </div>

                <div class="hidden print:block">
                    <h1 class="text-lg font-semibold">Laporan Bulanan VIWB ({{ periode }})</h1>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Total Tagihan</div>
                        <div class="mt-1 text-xl font-semibold text-gray-800">{{ formatRupiah(totalTagihan) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Sudah Lunas</div>
                        <div class="mt-1 text-xl font-semibold text-gray-800">{{ formatRupiah(totalLunas) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Pendapatan (diterima)</div>
                        <div class="mt-1 text-xl font-semibold text-green-600">{{ formatRupiah(totalPendapatan) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Total Pengeluaran</div>
                        <div class="mt-1 text-xl font-semibold text-red-600">{{ formatRupiah(totalPengeluaran) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Saldo</div>
                        <div class="mt-1 text-xl font-semibold" :class="saldo >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatRupiah(saldo) }}
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b p-4">
                        <h3 class="font-medium text-gray-800">Tagihan: {{ periode }}</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Jenis</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in tagihan" :key="t.id">
                                <td class="px-4 py-3">{{ t.warga.unit_id }}</td>
                                <td class="px-4 py-3">{{ t.warga.nama }}</td>
                                <td class="px-4 py-3">{{ t.jenis }}</td>
                                <td class="px-4 py-3 text-right">{{ formatRupiah(t.total) }}</td>
                                <td class="px-4 py-3">{{ t.status }}</td>
                            </tr>
                            <tr v-if="tagihan.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada tagihan periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b p-4">
                        <h3 class="font-medium text-gray-800">Pendapatan: {{ periode }}</h3>
                        <p class="text-xs text-gray-400">Pembayaran yang dikonfirmasi (uang benar-benar diterima) pada bulan ini.</p>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Tanggal Diterima</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in pendapatan" :key="p.id">
                                <td class="px-4 py-3">{{ p.warga.unit_id }}</td>
                                <td class="px-4 py-3">{{ p.warga.nama }}</td>
                                <td class="px-4 py-3">{{ formatTanggal(p.dikonfirmasi_at) }}</td>
                                <td class="px-4 py-3 text-right text-green-600">{{ formatRupiah(p.total_dibayar) }}</td>
                            </tr>
                            <tr v-if="pendapatan.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada pendapatan diterima bulan ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b p-4">
                        <h3 class="font-medium text-gray-800">Pengeluaran: {{ periode }}</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Kategori</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="p in pengeluaran" :key="p.id">
                                <td class="px-4 py-3">{{ p.kategori }}</td>
                                <td class="px-4 py-3">{{ formatTanggal(p.tanggal) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatRupiah(p.nominal) }}</td>
                            </tr>
                            <tr v-if="pengeluaran.length === 0">
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">Tidak ada pengeluaran periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
