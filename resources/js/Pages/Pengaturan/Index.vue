<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Shared/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Shared/Components/InputLabel.vue';
import InputError from '@/Shared/Components/InputError.vue';
import PrimaryButton from '@/Shared/Components/PrimaryButton.vue';
import SecondaryButton from '@/Shared/Components/SecondaryButton.vue';
import TextInput from '@/Shared/Components/TextInput.vue';
import Modal from '@/Shared/Components/Modal.vue';

const props = defineProps({
    transparansi: Object,
    layanan: Array,
});

const formTransparansi = useForm({
    laporan_terbuka_ke_warga: Boolean(props.transparansi?.laporan_terbuka_ke_warga),
    tagihan_terbuka_ke_warga: Boolean(props.transparansi?.tagihan_terbuka_ke_warga),
});

function simpanTransparansi() {
    formTransparansi.put(route('pengaturan.transparansi'), {
        preserveScroll: true,
    });
}

const editingLayanan = ref(null);
const formLayanan = useForm({
    nominal: 0,
    nominal_mobil: null,
    nominal_tanpa_mobil: null,
    cutoff_hari: 10,
    denda_harian: 0,
    denda_maksimal: 0,
    alert_tunggakan_bulan: 3,
});

function bukaEditLayanan(item) {
    editingLayanan.value = item;
    formLayanan.nominal = item.nominal ?? 0;
    formLayanan.nominal_mobil = item.nominal_mobil ?? null;
    formLayanan.nominal_tanpa_mobil = item.nominal_tanpa_mobil ?? null;
    formLayanan.cutoff_hari = item.cutoff_hari ?? 10;
    formLayanan.denda_harian = item.denda_harian ?? 0;
    formLayanan.denda_maksimal = item.denda_maksimal ?? 0;
    formLayanan.alert_tunggakan_bulan = item.alert_tunggakan_bulan ?? 3;
    formLayanan.clearErrors();
}

function simpanLayanan() {
    if (!editingLayanan.value) return;
    formLayanan.put(route('pengaturan.layanan', editingLayanan.value.id), {
        onSuccess: () => {
            editingLayanan.value = null;
        },
    });
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka ?? 0);
}
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pengaturan Sistem & Paguyuban
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

                <!-- Pengaturan Transparansi Paguyuban -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Keterbukaan Paguyuban (Transparansi)
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Kendali hak akses warga terhadap laporan keuangan kas dan tagihan warga lain.
                        </p>
                    </div>

                    <form @submit.prevent="simpanTransparansi" class="mt-6 space-y-6">
                        <div class="flex items-start gap-3">
                            <input
                                id="laporan_terbuka_ke_warga"
                                v-model="formTransparansi.laporan_terbuka_ke_warga"
                                type="checkbox"
                                class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <div>
                                <label for="laporan_terbuka_ke_warga" class="font-medium text-gray-800 cursor-pointer">
                                    Buka Laporan Keuangan & Neraca Kas ke Warga (Read-Only)
                                </label>
                                <p class="text-sm text-gray-500">
                                    Jika diaktifkan, seluruh warga yang login dapat melihat neraca kas, ringkasan saldo, pemasukan, dan rincian pengeluaran paguyuban secara read-only.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <input
                                id="tagihan_terbuka_ke_warga"
                                v-model="formTransparansi.tagihan_terbuka_ke_warga"
                                type="checkbox"
                                class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <div>
                                <label for="tagihan_terbuka_ke_warga" class="font-medium text-gray-800 cursor-pointer">
                                    Buka Daftar Tagihan Perumahan ke Warga
                                </label>
                                <p class="text-sm text-gray-500">
                                    Jika diaktifkan, warga dapat melihat status pembayaran dan tagihan warga lain di menu Tagihan untuk transparansi iuran lingkungan.
                                </p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <PrimaryButton :disabled="formTransparansi.processing">
                                {{ formTransparansi.processing ? 'Menyimpan...' : 'Simpan Pengaturan Transparansi' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Pengaturan Tarif Layanan & Denda -->
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Konfigurasi Tarif Layanan & Denda
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Atur nominal iuran pokok, tanggal cutoff jatuh tempo, dan batas denda keterlambatan per jenis layanan.
                        </p>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500">Layanan</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Nominal Pokok</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Cutoff (Tgl)</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Denda / Hari</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Maks. Denda</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Alert Tunggakan</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in layanan" :key="item.id">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ item.jenis }}
                                        <div v-if="item.jenis === 'KEAMANAN'" class="text-xs text-gray-500">
                                            Mobil: {{ formatRupiah(item.nominal_mobil) }} | Non-Mobil: {{ formatRupiah(item.nominal_tanpa_mobil) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-900">
                                        {{ formatRupiah(item.nominal) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        Tanggal {{ item.cutoff_hari }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ formatRupiah(item.denda_harian) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ formatRupiah(item.denda_maksimal) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        >= {{ item.alert_tunggakan_bulan }} bulan
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <SecondaryButton @click="bukaEditLayanan(item)">
                                            Ubah
                                        </SecondaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit Layanan -->
        <Modal :show="editingLayanan !== null" @close="editingLayanan = null" max-width="lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    Ubah Tarif: {{ editingLayanan?.jenis }}
                </h3>
                <form @submit.prevent="simpanLayanan" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="layanan_nominal" value="Nominal Pokok (Rp)" />
                        <TextInput
                            id="layanan_nominal"
                            v-model="formLayanan.nominal"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="formLayanan.errors.nominal" class="mt-1" />
                    </div>

                    <template v-if="editingLayanan?.jenis === 'KEAMANAN'">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="nominal_mobil" value="Tarif Mobil (Rp)" />
                                <TextInput
                                    id="nominal_mobil"
                                    v-model="formLayanan.nominal_mobil"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="formLayanan.errors.nominal_mobil" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel for="nominal_tanpa_mobil" value="Tarif Non-Mobil (Rp)" />
                                <TextInput
                                    id="nominal_tanpa_mobil"
                                    v-model="formLayanan.nominal_tanpa_mobil"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="formLayanan.errors.nominal_tanpa_mobil" class="mt-1" />
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="cutoff_hari" value="Batas Jatuh Tempo (Tgl)" />
                            <TextInput
                                id="cutoff_hari"
                                v-model="formLayanan.cutoff_hari"
                                type="number"
                                min="1"
                                max="28"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="formLayanan.errors.cutoff_hari" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="alert_tunggakan_bulan" value="Alert Tunggakan (Bulan)" />
                            <TextInput
                                id="alert_tunggakan_bulan"
                                v-model="formLayanan.alert_tunggakan_bulan"
                                type="number"
                                min="1"
                                max="12"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="formLayanan.errors.alert_tunggakan_bulan" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="denda_harian" value="Denda Harian (Rp)" />
                            <TextInput
                                id="denda_harian"
                                v-model="formLayanan.denda_harian"
                                type="number"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="formLayanan.errors.denda_harian" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="denda_maksimal" value="Maks. Denda (Rp)" />
                            <TextInput
                                id="denda_maksimal"
                                v-model="formLayanan.denda_maksimal"
                                type="number"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="formLayanan.errors.denda_maksimal" class="mt-1" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 pt-2">
                        <SecondaryButton @click="editingLayanan = null">Batal</SecondaryButton>
                        <PrimaryButton :disabled="formLayanan.processing">
                            {{ formLayanan.processing ? 'Menyimpan...' : 'Simpan Tarif' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
