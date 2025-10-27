<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    show_boxShasow: {
        type: Boolean,
        default: true
    },
});


// État pour gérer l'ouverture du dropdown
const filterDropdownOpen = ref(false);
// Fonction pour appliquer les filtres
const applyFilters = () => {
    emit('search', props.modelValue);
};
// Fonction pour fermer le dropdown
const closeDropdown = () => {
    filterDropdownOpen.value = false;
};
// Fonction pour mettre à jour les filtres
const updateFilter = (key, value) => {
    const updatedValue = { ...props.modelValue, [key]: value };
    emit('update:modelValue', updatedValue);
};
const emit = defineEmits(["search", "reset", "update:modelValue"]);
</script>

<template>
    <div class="filter-container bg-white p-4 rounded-t-md" :class="show_boxShasow ? 'shadow' : ''">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center  w-full  flex-grow">
                <!-- Barre de recherche -->
                <a-input size="large" v-model:value="props.modelValue.search" placeholder="Rechercher..." allow-clear @pressEnter="$emit('search', props.modelValue)"
                        class="group w-full !rounded-r-none "
                >
                    <template #prefix>
                        <font-awesome-icon icon="fa-solid fa-magnifying-glass"
                                class="text-sm group-focus-within:text-secondary text-slate-400 transition-colors duration-300"
                        />
                    </template>
                </a-input>
                <!-- Bouton Filter avec Dropdown - Affiché seulement s'il y a des filtres supplémentaires -->
                <a-dropdown v-if="$slots.otherFilter" v-model:open="filterDropdownOpen" placement="bottomLeft" trigger="click" :overlay-style="{ width: '400px' }">
                    <a-button size="large" type="default" class="!rounded-none border-r-0 focus:z-10">
                        <font-awesome-icon icon="fa-solid fa-filter" />
                    </a-button>
                    <!-- contenu du dropdown des filtres -->
                    <template #overlay>
                        <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 !mt-0">
                            <div class="space-y-3">
                                <!-- Slot pour les filtres personnalisés -->
                                <slot name="otherFilter" :value="props.modelValue" :updateFilter="updateFilter" :apply="applyFilters" :close="closeDropdown"/>
                                <!-- Boutons d'action appliquer filtre/ fermer dropdown -->
                                <div class="flex gap-2 pt-2 mt-3">
                                    <a-button type="primary" @click="applyFilters" class="flex-1"> Appliquer </a-button>
                                    <a-button type="default" @click="closeDropdown" class="flex-1"> Fermer </a-button>
                                </div>
                            </div>
                        </div>
                    </template>
                </a-dropdown>

                <!-- Boutons de recherche -->
                <a-button
                    size="large"
                    @click="$emit('reset', props.modelValue)"
                    type="default"
                    class="group/reset focus:z-10 overflow-hidden relative"
                    :class="$slots.otherFilter ? '!rounded-none border-r-0' : '!rounded-none border-r-0'"
                >
                    <font-awesome-icon
                        class="cursor-pointer group-hover/reset:text-secondary group-hover/reset:rotate-180 transition-all duration-500"
                        icon="fa-solid fa-refresh"
                    />
                </a-button>
                <a-button
                    size="large"
                    @click="$emit('search', props.modelValue)"
                    type="default"
                    class="group/reset focus:z-10"
                    :class="[
                        $slots.otherFilter ? '!rounded-l-none' : '!rounded-l-none',
                        !$slots.import ? '' : '!rounded-r-none'
                    ]"
                >
                    <span class="i-fa6-solid-magnifying-glass"></span>
                    Rechercher
                </a-button>
                <slot name="import"/>
            </div>

            <!-- Bouton Ajouter -->
            <div class="w-full sm:w-auto">
                <!-- Slot pour bouton ajouter -->
                <div class="flex items-center justify-center gap-2 w-full">
                    <slot name="add"/>
                </div>
            </div>
        </div>
    </div>
</template>
