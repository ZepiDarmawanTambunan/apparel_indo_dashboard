<script setup lang="ts">

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md">
            <header class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-gray-800">{{ title }}</h1>
                <p class="text-sm text-gray-600">{{ description }}</p>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
