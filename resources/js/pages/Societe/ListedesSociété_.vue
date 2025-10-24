<template>
    <AppSidebarLayout>
        <div class="min-h-screen sm:p-4">
            <div class="mx-auto w-full ">
                <!-- Header -->
                <a-card bordered :body-style="{ padding: '0' }" class="animate-fade-in-down">
                    <BaseFilter v-model="filter" @search="applyFilters" @reset="resetFilters">
                        <template #add>
                            <a-segmented v-model:value="currentView" :options="[ { label: 'Tableau', value: 'table' }, { label: 'Dossier', value: 'dossier' }]"
                                size="large"
                                class="switch-segmented"
                            />
                            <ButtonIcon
                                @click="() => formSociete.add()"
                                type="primary"
                                text="Nouveau Societe"
                                icon="fa-plus"
                            />
                        </template>
                        <template #otherFilter="{ value, updateFilter, apply, close }">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Statut</label>
                                    <a-select :value="value.status" size="large"  @change="(val) => updateFilter('status', val)" allow-clear
                                        placeholder="Tous les statuts"
                                        class="w-full"
                                    >
                                        <a-select-option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </a-select-option>
                                    </a-select>
                                </div>
                            </div>
                        </template>
                    </BaseFilter>
                </a-card>
                <!-- Table -->
                <div v-if="currentView === 'table'" key="table-view" class="animate-fade-in-up  overflow-visible rounded-b-lg bg-white shadow-sm">
                    <BaseTable :data="props.data" :columns="TableColumns" :actions="TableActions" :endpoint="route('societe.index')"/>
                </div>
                <div v-else-if="currentView === 'dossier'" key="dossier-view"> Vue dossier </div>
            </div>
        </div>
        <FormSociete_ ref="formSociete" />
    </AppSidebarLayout>
</template>

<script setup>
import BaseTable from '@/components/BaseTable/BaseTable.vue';
//import Bouton from '@/components/utils/Bouton.vue';
import { createSearchFilter, gotoSearch } from "@/../Utils/FilterUtils";
import BaseFilter from '@/components/BaseFilter/BaseFilter.vue';
import ButtonIcon from '@/components/Button/ButtonIcon.vue';
import ImagePreview from '@/components/ImagePreview/ImagePreview.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Tag } from 'ant-design-vue';
import { h, ref } from 'vue';
import FormSociete_ from './FormSociete_.vue';
const props = defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});
//Filtre
const filter = ref({...createSearchFilter(),status:props.filters.status || null});
const applyFilters = (data) => {
    const url = route("societe.index");
    gotoSearch(filter.value, url);
};
const resetFilters = () => {
    filter.value = {...createSearchFilter(),status : null};
    applyFilters();
};



// Format pour CustomTable
const TableColumns = [
    { key: 'photo', label: 'Logo', width: 80, align: 'center',
        render: (record) => h(ImagePreview, {
            thumbnailUrl: record.thumbnail_url || '/img/placeholder_img.png',
            imageUrl: record.image_url || '/img/placeholder_img.png',
            alt: record.name
        })
    },
    { key: 'name', label: 'Nom', width: 120 },
    { key: 'secteur', label: "Secteur", width: 100 },
    { key: 'nif', label: 'NIF', width: 100 },
    { key: 'stat', label: 'STAT', width: 100 },
    { key: 'telephone', label: 'Téléphone', width: 100 },
    { key: 'email', label: 'Email', width: 100},
    { key: 'status', label: 'Statut', align: 'center', width: 100,
        render: (record) => h( Tag, { color: getStatusColor(record.status), style: { margin: 0 }}, record.status),
    },
    { key: 'adresse', label: 'Adresse', width: 150 },
];
// Actions pour CustomTable
const TableActions = [
    { label: 'Archiver', icon: 'eye', onClick: (item) => formSociete.value.update(item),},
    { label: 'Modifier', icon: 'pen', onClick: (item) => formSociete.value.update(item),},
    { label: 'Supprimer', icon: 'trash', onClick: (item) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la société "${item.name}" ?`)) {
            // Ici vous pouvez ajouter la logique de suppression
            console.log('Supprimer:', item)
        }
    }},
];

// Options pour le filtre statut
const statusOptions = [
    { label: 'Actif', value: 'Actif' },
    { label: 'Inactif', value: 'Inactif' },
    { label: 'Suspendu', value: 'Suspendu' }
];

// Couleur selon Status
function getStatusColor(status) {
    if (status === 'Actif') return 'green';
    if (status === 'Inactif') return 'red';
    if (status === 'Suspendu') return 'orange';
    return 'default';
}

const formSociete = ref(null);

// pour changer de type de vue
const currentView = ref("table");

// // Handle checkbox selection
// function handleSelectionChange(keys) {
//     selectedIds.value = keys;
// }

// // Handle selected items action
// function handleSelectedItems() {
//     alert(`Actions sur les éléments sélectionnés: ${selectedIds.value.join(', ')}`);
// }
</script>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.6s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease;
}

.animate-slide-in {
    animation: slideIn 0.5s ease-out both;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
