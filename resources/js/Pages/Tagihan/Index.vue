<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Shared/Components/InputError.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';
import Pagination from '@/Shared/Components/Pagination.vue';
import { formatStatus } from '@/lib/utils';

const props = defineProps({
    tagihan: [Object, Array],
    filters: Object,
    canGenerate: Boolean,
});

const items = computed(() => Array.isArray(props.tagihan) ? props.tagihan : (props.tagihan?.data ?? []));

const periodeFilter = ref(props.filters.periode ?? '');
const jenisFilter = ref(props.filters.jenis ?? '');
const statusFilter = ref(props.filters.status ?? '');

function terapkanFilter() {
    router.get(
        route('tagihan.index'),
        {
            periode: periodeFilter.value || undefined,
            jenis: jenisFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

const generateForm = useForm({
    periode: new Date().toISOString().slice(0, 7),
});

function generate() {
    generateForm.post(route('tagihan.generate'));
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
}

function formatTanggal(tanggal) {
    return tanggal ? tanggal.slice(0, 10) : '-';
}
</script>

<template>
    <Head title="Tagihan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tagihan
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

                <div v-if="canGenerate" class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="mb-4 font-medium text-gray-800">Generate Tagihan Bulanan</h3>
                    <form @submit.prevent="generate" class="flex flex-wrap items-end gap-4">
                        <div>
                            <InputLabel for="periode_generate" value="Periode (YYYY-MM)" />
                            <TextInput
                                id="periode_generate"
                                v-model="generateForm.periode"
                                type="month"
                                class="mt-1 block"
                            />
                            <InputError :message="generateForm.errors.periode" class="mt-1" />
                        </div>
                        <PrimaryButton :disabled="generateForm.processing">
                            {{ generateForm.processing ? 'Memproses...' : 'Generate' }}
                        </PrimaryButton>
                    </form>
                </div>

                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <InputLabel for="periode" value="Filter Periode" />
                            <TextInput
                                id="periode"
                                v-model="periodeFilter"
                                placeholder="2026-09"
                                class="mt-1 block w-32"
                                @keyup.enter="terapkanFilter"
                            />
                        </div>
                        <div>
                            <InputLabel for="jenis" value="Filter Jenis" />
                            <select
                                id="jenis"
                                v-model="jenisFilter"
                                class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm"
                                @change="terapkanFilter"
                            >
                                <option value="">Semua</option>
                                <option value="KEAMANAN">KEAMANAN</option>
                                <option value="PAGUYUBAN">PAGUYUBAN</option>
                                <option value="HIPPAM">HIPPAM</option>
                                <option value="KEBERSIHAN">KEBERSIHAN</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="status" value="Filter Status" />
                            <select
                                id="status"
                                v-model="statusFilter"
                                class="mt-1 block rounded-md border-gray-300 text-sm shadow-sm"
                                @change="terapkanFilter"
                            >
                                <option value="">Semua</option>
                                <option value="BELUM_BAYAR">BELUM_BAYAR</option>
                                <option value="SEBAGIAN">SEBAGIAN</option>
                                <option value="LUNAS">LUNAS</option>
                            </select>
                        </div>
                        <SecondaryButton @click="terapkanFilter">Terapkan</SecondaryButton>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Daftar Tagihan Iuran">
                        <caption class="sr-only">Tabel Tagihan Iuran Warga</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Warga</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Jenis</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Periode</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Jatuh Tempo</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500">Denda</th>
                                <th scope="col" class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in items" :key="t.id">
                                <td class="px-4 py-3">{{ t.warga?.unit_id ?? '-' }} - {{ t.warga?.nama ?? 'Warga Nonaktif' }}</td>
                                <td class="px-4 py-3">{{ t.jenis }}</td>
                                <td class="px-4 py-3">{{ t.periode }}</td>
                                <td class="px-4 py-3">{{ formatTanggal(t.tanggal_jatuh_tempo) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatRupiah(t.nominal) }}</td>
                                <td class="px-4 py-3 text-right" :class="t.denda > 0 ? 'text-red-600' : ''">
                                    {{ formatRupiah(t.denda) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatRupiah(t.total) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                             'bg-green-100 text-green-800': t.status === 'LUNAS',
                                            'bg-yellow-100 text-yellow-800': t.status === 'SEBAGIAN',
                                            'bg-red-100 text-red-800': t.status === 'BELUM_BAYAR',
                                        }"
                                    >
                                        {{ formatStatus(t.status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-medium text-gray-700">Belum ada data tagihan</p>
                                        <p class="text-xs text-gray-400 mt-1">Daftar tagihan bulanan warga akan ditampilkan di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :links="tagihan.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
