<script setup>
import ApplicationLogo from '@/Shared/Components/ApplicationLogo.vue';
import ThemeToggle from '@/Shared/Components/ThemeToggle.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="relative flex min-h-screen flex-col items-center bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 pt-6 sm:justify-center sm:pt-0 transition-colors duration-200"
    >
        <div class="absolute top-4 right-4">
            <ThemeToggle />
        </div>

        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-700 dark:text-gray-200" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white dark:bg-gray-800 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg border border-transparent dark:border-gray-700/60"
        >
            <slot />
        </div>
    </div>
</template>
