<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';

const props = defineProps({
    tagihan: Array,
    filters: Object,
    canGenerate: Boolean,
});

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
                        </div>
                        <PrimaryButton :disabled="generateForm.processing">Generate</PrimaryButton>
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
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Warga</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Jenis</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Periode</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Nominal</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Denda</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in tagihan" :key="t.id">
                                <td class="px-4 py-3">{{ t.warga.unit_id }} - {{ t.warga.nama }}</td>
                                <td class="px-4 py-3">{{ t.jenis }}</td>
                                <td class="px-4 py-3">{{ t.periode }}</td>
                                <td class="px-4 py-3">{{ formatTanggal(t.tanggal_jatuh_tempo) }}</td>
                                <td class="px-4 py-3 text-right">{{ formatRupiah(t.nominal) }}</td>
                                <td class="px-4 py-3 text-right" :class="t.denda > 0 ? 'text-red-600' : ''">
                                    {{ formatRupiah(t.denda) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatRupiah(t.total) }}</td>
                                <td class="px-4 py-3">{{ t.status }}</td>
                            </tr>
                            <tr v-if="tagihan.length === 0">
                                <td colspan="8" class="px-4 py-6 text-center text-gray-400">
                                    Belum ada tagihan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
