import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}

export const STATUS_LABELS = {
    // Pembayaran
    MENUNGGU_KONFIRMASI: 'Menunggu Konfirmasi',
    DIKONFIRMASI: 'Dikonfirmasi',
    DITOLAK: 'Ditolak',

    // Pengeluaran
    DRAFT: 'Draft',
    MENUNGGU_APPROVAL: 'Menunggu Persetujuan',
    APPROVED: 'Disetujui',
    DISETUJUI: 'Disetujui',
    REJECTED: 'Ditolak',

    // Tagihan
    BELUM_BAYAR: 'Belum Bayar',
    SEBAGIAN: 'Sebagian',
    LUNAS: 'Lunas',

    // Warga
    AKTIF: 'Aktif',
    PINDAH: 'Pindah',
    KONTRAK: 'Kontrak',
};

export function formatStatus(status) {
    if (!status) return '-';
    return STATUS_LABELS[status] || status;
}

