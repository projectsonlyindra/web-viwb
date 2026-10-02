<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Shared/Components/ApplicationLogo.vue';
import Dropdown from '@/Shared/Components/Dropdown.vue';
import DropdownLink from '@/Shared/Components/DropdownLink.vue';
import NavLink from '@/Shared/Components/NavLink.vue';
import ResponsiveNavLink from '@/Shared/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <!-- Hamburger (Mobile Left) -->
                        <div class="-ms-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex h-11 w-11 items-center justify-center rounded-md p-2 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:bg-gray-100 focus:text-gray-700 focus:outline-none"
                                aria-label="Menu"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Brand & Nav (Center on Mobile, Left on Desktop) -->
                        <div class="flex flex-1 items-center justify-center sm:flex-initial sm:justify-start">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links (Desktop) -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role !== 'WARGA'"
                                    :href="route('warga.index')"
                                    :active="route().current('warga.*')"
                                >
                                    Warga
                                </NavLink>
                                <NavLink
                                    :href="route('tagihan.index')"
                                    :active="route().current('tagihan.*')"
                                >
                                    Tagihan
                                </NavLink>
                                <NavLink
                                    :href="route('pembayaran.index')"
                                    :active="route().current('pembayaran.*')"
                                >
                                    Pembayaran
                                </NavLink>
                                <NavLink
                                    v-if="['SUPERADMIN', 'KETUA_RT', 'BENDAHARA'].includes($page.props.auth.user.role) || $page.props.pengaturan?.laporan_terbuka_ke_warga"
                                    :href="route('pengeluaran.index')"
                                    :active="route().current('pengeluaran.*')"
                                >
                                    Pengeluaran
                                </NavLink>
                                <NavLink
                                    :href="route('pengumuman.index')"
                                    :active="route().current('pengumuman.*')"
                                >
                                    Pengumuman
                                </NavLink>
                                <NavLink
                                    v-if="['SUPERADMIN', 'KETUA_RT', 'BENDAHARA'].includes($page.props.auth.user.role) || $page.props.pengaturan?.laporan_terbuka_ke_warga"
                                    :href="route('laporan.index')"
                                    :active="route().current('laporan.*')"
                                >
                                    Laporan
                                </NavLink>
                                <NavLink
                                    v-if="['SUPERADMIN', 'KETUA_RT'].includes($page.props.auth.user.role)"
                                    :href="route('pengaturan.index')"
                                    :active="route().current('pengaturan.*')"
                                >
                                    Pengaturan
                                </NavLink>
                            </div>
                        </div>

                        <!-- Right: Desktop Settings Dropdown -->
                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Keluar
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Mobile Right Spacer (Balances Hamburger on left so Logo is truly centered) -->
                        <div class="h-11 w-11 sm:hidden"></div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role !== 'WARGA'"
                            :href="route('warga.index')"
                            :active="route().current('warga.*')"
                        >
                            Warga
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('tagihan.index')"
                            :active="route().current('tagihan.*')"
                        >
                            Tagihan
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('pembayaran.index')"
                            :active="route().current('pembayaran.*')"
                        >
                            Pembayaran
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="['SUPERADMIN', 'KETUA_RT', 'BENDAHARA'].includes($page.props.auth.user.role) || $page.props.pengaturan?.laporan_terbuka_ke_warga"
                            :href="route('pengeluaran.index')"
                            :active="route().current('pengeluaran.*')"
                        >
                            Pengeluaran
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('pengumuman.index')"
                            :active="route().current('pengumuman.*')"
                        >
                            Pengumuman
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="['SUPERADMIN', 'KETUA_RT', 'BENDAHARA'].includes($page.props.auth.user.role) || $page.props.pengaturan?.laporan_terbuka_ke_warga"
                            :href="route('laporan.index')"
                            :active="route().current('laporan.*')"
                        >
                            Laporan
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="['SUPERADMIN', 'KETUA_RT'].includes($page.props.auth.user.role)"
                            :href="route('pengaturan.index')"
                            :active="route().current('pengaturan.*')"
                        >
                            Pengaturan
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Keluar
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" role="main">
                <div v-if="$page.props.flash?.error" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                        {{ $page.props.flash.error }}
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
