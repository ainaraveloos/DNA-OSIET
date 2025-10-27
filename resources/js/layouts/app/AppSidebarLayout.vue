<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';

import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import AppSidebarMenu from '@/components/AppSidebarMenu.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

import { onMounted, ref } from 'vue';

const scrollContainer = ref<HTMLElement | null>(null)
const isScrolled = ref(false)

onMounted(() => {
    scrollContainer.value?.addEventListener('scroll', () => {
        isScrolled.value = scrollContainer.value!.scrollTop > 0
    })
})

</script>

<template>
    <AppShell variant="sidebar">

        <AppContent variant="sidebar" class="overflow-x-hidden">

            <div class="fixed top-0 left-0 right-0 z-20">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            </div>
            <div class="fixed top-16 left-0 right-0 z-10 transition-shadow duration-300"
                :class="{ 'shadow-lg': isScrolled }">
                <AppSidebarMenu class="flex" />
            </div>

            <div ref="scrollContainer" class="mt-40 px-2 lg:px-0 md:px-0 overflow-y-auto h-[calc(100vh-7rem)]">
                <slot />
            </div>
        </AppContent>
    </AppShell>
</template>
