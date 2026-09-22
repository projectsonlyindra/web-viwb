<script setup>
import InputError from '@/Shared/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    pengumuman: {
        type: Array,
        default: () => [],
    },
});

function formatTanggal(tanggal) {
    return tanggal
        ? new Date(tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
        : '';
}

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="flex min-h-screen flex-col lg:flex-row">
        <!-- Panel brand: properti & pengumuman -->
        <div
            class="relative flex flex-col justify-between overflow-hidden px-8 py-10 text-white sm:px-12 sm:py-12 lg:w-[44%] lg:min-h-screen lg:px-14 lg:py-14"
            style="background: linear-gradient(160deg, #16453c 0%, #0e2f28 100%);"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.07]"
                style="background-image: repeating-linear-gradient(0deg, #fff 0, #fff 1px, transparent 1px, transparent 40px), repeating-linear-gradient(90deg, #fff 0, #fff 1px, transparent 1px, transparent 40px);"
            ></div>

            <div class="relative">
                <div class="flex items-center gap-2.5">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 2L28 12.5V28H18V19H12V28H2V12.5L15 2Z" fill="#D9711F" />
                    </svg>
                    <span class="text-xl font-extrabold tracking-tight">VIWB</span>
                </div>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-white/65">
                    Sistem administrasi perumahan: tagihan, pembayaran, kas, dan informasi warga dalam satu tempat.
                </p>
            </div>

            <div v-if="pengumuman.length > 0" class="relative mt-10 lg:mt-0">
                <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-white/50">
                    Pengumuman
                </h2>
                <div class="space-y-2.5">
                    <div
                        v-for="p in pengumuman"
                        :key="p.id"
                        class="rounded-lg border border-white/10 bg-white/[0.06] p-4 backdrop-blur-sm"
                    >
                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="text-sm font-semibold text-white">{{ p.judul }}</h3>
                            <span class="shrink-0 text-[11px] text-white/45">{{ formatTanggal(p.created_at) }}</span>
                        </div>
                        <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-white/60">{{ p.isi }}</p>
                    </div>
                </div>
            </div>
            <p v-else class="relative text-xs text-white/35">
                Belum ada pengumuman terbaru.
            </p>
        </div>

        <!-- Panel form login -->
        <div class="flex flex-1 items-center justify-center bg-gray-50 px-6 py-12 sm:px-10">
            <div class="w-full max-w-sm">
                <h1 class="text-2xl font-bold text-gray-900">Selamat datang</h1>
                <p class="mt-1.5 text-sm text-gray-500">Masuk untuk mengakses sistem administrasi perumahan.</p>

                <div v-if="status" class="mt-6 rounded-md bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="mt-1.5 block w-full rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-[#16453c] focus:ring-[#16453c]"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="mt-1.5 block w-full rounded-lg border-gray-300 text-sm shadow-sm transition focus:border-[#16453c] focus:ring-[#16453c]"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                v-model="form.remember"
                                class="rounded border-gray-300 text-[#16453c] shadow-sm focus:ring-[#16453c]"
                            />
                            <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-gray-500 underline hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#16453c] focus:ring-offset-2"
                        >
                            Lupa password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="{ 'opacity-60': form.processing }"
                        class="flex w-full items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed"
                        style="background-color: #d9711f; --tw-ring-color: #d9711f;"
                    >
                        Log In
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
