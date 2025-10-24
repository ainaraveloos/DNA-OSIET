<template>
    <AppSidebarLayout>
        <div class="min-h-screen sm:p-4">
            <div class="mx-auto w-full ">
                <!-- Header -->
                <a-card bordered :body-style="{ padding: '0' }" class="animate-fade-in-down">
                   <BaseFilter v-model="props.filters" @search="applyFilters" @reset="resetFilters">
                    <template #add>
                <ButtonIcon
                    @click="() => formSociete.add()"
                    type="primary"
                    text="Nouveau Societe"
                    icon="fa-plus"
                />
            </template>
                    </BaseFilter>
                </a-card>

                <!-- CustomTable Desktop -->
                <div class="animate-fade-in-up hidden overflow-visible rounded-b-lg bg-white shadow-sm lg:block">
                    <BaseTable
                        :data="props.data"
                        :columns="TableColumns"
                        :actions="TableActions"
                        :endpoint="route('societe.index')"
                    />
                </div>
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
const filter = ref({...createSearchFilter()});
const applyFilters = (data) => {
    const url = route("societe.index");
    gotoSearch(filter.value, url);
};
const resetFilters = () => {
    filter.value = {...createSearchFilter()};
    applyFilters();
};

// Format pour CustomTable
const TableColumns = [
    {
        key: 'photo',
        label: 'Logo',
        width: 80,
        align: 'center',
        render: (record) => h(ImagePreview, {
            thumbnailUrl: record.thumbnail_url || '/img/placeholder_img.png',
            imageUrl: record.image_url || '/img/placeholder_img.png',
            alt: `Logo - ${record.name}`
        })
    },
    { key: 'name', label: 'Nom de la Société', width: 150 },
    { key: 'secteur', label: "Secteur d'Activité", width: 120 },
    { key: 'nif', label: 'NIF', width: 100 },
    { key: 'stat', label: 'STAT', width: 100 },
    { key: 'telephone', label: 'Téléphone', width: 120 },
    { key: 'email', label: 'Email', width: 150 },
    { key: 'status', label: 'Statut', align: 'center', width: 120,
        render: (record) =>
            h(
                Tag,
                {
                    color: getStatusColor(record.status),
                    style: { margin: 0 },
                },
                record.status,
            ),
    },
    { key: 'adresse', label: 'Adresse', width: 200 },
];


// Actions pour CustomTable
const TableActions = [
    { label: 'Voir', icon: 'eye', onClick: (item) => formSociete.value.update(item),},
    { label: 'Modifier', icon: 'pen', onClick: (item) => formSociete.value.update(item),},
    { label: 'Supprimer', icon: 'trash', onClick: (item) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer la société "${item.name}" ?`)) {
            // Ici vous pouvez ajouter la logique de suppression
            console.log('Supprimer:', item)
        }
    }},
];

// Couleur selon Status
function getStatusColor(status) {
    if (status === 'Actif') return 'green';
    if (status === 'Inactif') return 'red';
    if (status === 'Suspendu') return 'orange';
    return 'default';
}

function handleAddSociete() {

}

const formSociete = ref(null);

function openForm() {
    if (formSociete.value) {
        formSociete.value.open();
    }
}

// Handle checkbox selection
function handleSelectionChange(keys) {
    selectedIds.value = keys;
}

// Handle selected items action
function handleSelectedItems() {
    alert(`Actions sur les éléments sélectionnés: ${selectedIds.value.join(', ')}`);
}
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
