<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 sm:px-6">
        <div class="flex justify-between flex-1 sm:hidden">
            <template v-for="(link, index) in [links[0], links[links.length - 1]]" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md cursor-not-allowed select-none"
                    v-html="link.label"
                />
            </template>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <template v-for="(link, key) in links" :key="key">
                    <span
                        v-if="link.url === null"
                        class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 cursor-not-allowed first:rounded-l-md last:rounded-r-md select-none"
                        v-html="link.label"
                    />
                    <Link
                        v-else
                        :href="link.url"
                        class="relative inline-flex items-center px-3.5 py-2 text-sm font-medium border first:rounded-l-md last:rounded-r-md transition-colors focus:z-20 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        :class="link.active
                            ? 'z-10 bg-indigo-50 dark:bg-indigo-950/60 border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-300 font-semibold'
                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100'"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </div>
</template>
