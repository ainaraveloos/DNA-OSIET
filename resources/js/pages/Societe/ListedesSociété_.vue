<template>
    <AppSidebarLayout>
      <div class="min-h-screen sm:p-4">
        <div class="w-full mx-auto border-1 border-gray-400/25 rounded-lg">

          <!-- Header -->
          <a-card bordered :body-style="{ padding: '0' }" class="animate-fade-in-down">
            <div
              class="flex flex-col md:flex-row lg:flex-row md:justify-between lg:justify-between gap-3 sm:gap-4 p-3 sm:p-4">

              <!-- Filtres -->
              <div
                class="flex flex-wrap md:flex-row md:w-full md:max-w-[330px] items-center justify-between lg:justify-between md:gap-1.5">
                <a-button type="default" icon>
                  Filter
                </a-button>

                <a-button v-for="(filter, i) in filters" :key="i"
                  :type="activeFilter === filter.value ? 'primary' : 'default'" @click="activeFilter = filter.value"
                  class="px-2.5 py-1.5 sm:px-3 lg:text-[14px] sm:py-2 text-xs sm:text-sm font-medium rounded-md">
                  {{ filter.label }}
                </a-button>

              </div>

              <!-- Search + Bouton -->
              <div class="flex flex-row gap-2">
                <a-input-search @search="showModal" placeholder="Rechercher..." />
                 <!-- Bouton d'action pour les éléments sélectionnés -->
                 <a-button
                  v-if="selectedIds.length > 0"
                  type="default"
                  @click="handleSelectedItems"
                  class="px-2.5 py-1.5 sm:px-3 lg:text-[14px] sm:py-2 text-xs sm:text-sm font-medium rounded-md">
                  Actions ({{ selectedIds.length }})
                </a-button>
                <Bouton @click="openForm" label="Nouveau societe" icon="plus" />
              </div>
            </div>
          </a-card>

          <!-- CustomTable Desktop -->
          <div class=" hidden lg:block bg-white rounded-b-lg shadow-sm overflow-visible animate-fade-in-up">
            <BaseTable
              :data="tableDataFormatted"
              :columns="customTableColumns"
              :actions="customTableActions"
              :endpoint="route('societe.index')"
              :selectable-rows="true"
              :selected-ids="selectedIds"
              @selection-change="handleSelectionChange"
            />
          </div>
          <!-- Mobile / Tablet Cards -->
        <div class="lg:hidden bg-white rounded-b-lg shadow-sm animate-fade-in-up">
          <div class="divide-y divide-gray-200">
            <div v-for="(item, index) in filteredData" :key="item.id"
              class="p-4 hover:bg-gray-50 transition-colors animate-slide-in"
              :style="{ animationDelay: `${index * 0.1}s` }">
              <div class="flex flex-col space-y-3">
                <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 truncate">{{ item.nom }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ item.secteur }}</p>
                  </div>
                  <a-tag :color="getStatusColor(item.statut)">
                    {{ item.statut }}
                  </a-tag>
                </div>
                <div class="grid grid-cols-2 text-center text-xs">
                  <div class="text-left">
                    <span class="text-gray-500">Employés:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ item.employes }}</span>
                  </div>
                  <div class="text-right">
                    <span class="text-gray-500">Cotisation:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ item.cotisation }}</span>
                  </div>
                </div>

                <!-- Actions Ellipsis -->
                <div class="flex items-center justify-center lg:justify-end pt-2 border-t border-gray-100 relative">
                  <a-dropdown trigger="manual" :visible="dropdownVisibleMap[item.id]">
                    <a-button type="text" @click="dropdownVisibleMap[item.id] = !dropdownVisibleMap[item.id]">
                      <EllipsisOutlined style="color: black; font-size: 20px;" />
                    </a-button>

                    <template #overlay>
                      <a-menu @mouseenter="isHovering[item.id] = true"
                        @mouseleave="isHovering[item.id] = false; dropdownVisibleMap[item.id] = false">
                        <a-menu-item v-for="(action, i) in actions" :key="i" @click="action.handler(item)">
                          <component :is="action.icon" class="w-4 h-4 mr-2 text-black" />
                          {{ action.label }}
                        </a-menu-item>
                      </a-menu>
                    </template>
                  </a-dropdown>
                </div>
              </div>
            </div>
          </div>
        </div>


        </div>
      </div>

      <FormSociete_ ref="formSociete" />

    </AppSidebarLayout>
  </template>

  <script setup lang="ts">
  import BaseTable from "@/components/BaseTable/BaseTable.vue";
import Bouton from "@/components/utils/Bouton.vue";
import AppSidebarLayout from "@/layouts/app/AppSidebarLayout.vue";
import type { FormInstance } from "ant-design-vue";
import { Tag } from "ant-design-vue";
import { computed, h, reactive, ref } from "vue";
import FormSociete_ from './FormSociete_.vue';


  interface TableItem {
    id: number;
    nom: string;
    secteur: string;
    employes: string | number;
    statut: string;
    cotisation: string;
  }

  interface Filter {
    label: string;
    value: string;
  }

  interface Action {
    label: string;
    icon: any;
    handler: (item: TableItem) => void;
  }

  const search = ref("");
  const activeFilter = ref("all");
  const showModal = ref(false);
  const newSociete = ref<TableItem>({ id: 0, nom: "", secteur: "", employes: 0, statut: "Actif", cotisation: "" });
  const formRef = ref<FormInstance | null>(null);

  // Checkbox selection
  const selectedIds = ref<(string | number)[]>([]);

  // Dropdowns visibility
  const dropdownVisibleMap = reactive<Record<number, boolean>>({});
  const isHovering = reactive<Record<number, boolean>>({});

  const filters = ref<Filter[]>([
    { label: "Toutes", value: "all" },
    { label: "Archives", value: "archive" },
    { label: "En retard", value: "late" }
  ]);

  let nextId = 1;

  const tableData = ref<TableItem[]>([
    { id: nextId++, nom: "DNA WEBHOSTING 1", secteur: "Développement App", employes: 100, statut: "Actif", cotisation: "01/01/2025" },
    { id: nextId++, nom: "DNA WEBHOSTING 2", secteur: "Développement App", employes: 250, statut: "En retard", cotisation: "01/01/2025" },
    { id: nextId++, nom: "DNA WEBHOSTING 3", secteur: "Développement App", employes: 70, statut: "Archive", cotisation: "01/01/2025" }
  ]);

  const actions = ref<Action[]>([
    { label: "Voir", icon: { render() { return h("i", { class: "fa-solid fa-eye" }); } }, handler: (item) => alert("Voir " + item.nom) },
    { label: "Modifier", icon: { render() { return h("i", { class: "fa-solid fa-pen" }); } }, handler: (item) => alert("Modifier " + item.nom) },
    { label: "Supprimer", icon: { render() { return h("i", { class: "fa-solid fa-trash" }); } }, handler: (item) => alert("Supprimer " + item.nom) }
  ]);

  const tableColumns = [
    { title: "Nom de la Société", dataIndex: "nom", key: "nom", align: "center" },
    { title: "Secteur d'Activité", dataIndex: "secteur", key: "secteur", align: "center" },
    { title: "Nombre d'Employés", dataIndex: "employes", key: "employes", align: "center" },
    { title: "Statut", dataIndex: "statut", key: "statut", align: "center" },
    { title: "Dernière Cotisation", dataIndex: "cotisation", key: "cotisation", align: "center" },
    { title: "Actions", key: "actions", align: "center" }
  ];

  // Format pour CustomTable
  const customTableColumns = [
    { key: "nom", label: "Nom de la Societe",width:100  },
    { key: "secteur", label: "Secteur d'Activite",width:100  },
    { key: "employes", label: "Nombre d'Employes", align: "right" as const , width: 150 },
    {
      key: "statut",
      label: "Statut",
      align: "center" as const,
      width: 120,
      render: (record: TableItem) => h(Tag, {
        color: getStatusColor(record.statut),
        style: { margin: 0 }
      }, record.statut)
    },
    { key: "cotisation", label: "Derniere Cotisation", align: "center" as const ,width:100  }
  ];

  // Format des données pour CustomTable
  const tableDataFormatted = computed(() => ({
    data: filteredData.value,
    current_page: 1,
    last_page: 1,
    per_page: filteredData.value.length,
    total: filteredData.value.length
  }));

  // Actions pour CustomTable
  const customTableActions = [
    {
      label: "Voir",
      icon: "eye",
      onClick: (item: TableItem) => alert("Voir " + item.nom),
      //classStyle: "text-blue-600"
    },
    {
      label: "Modifier",
      icon: "pen",
      onClick: (item: TableItem) => alert("Modifier " + item.nom),
      //classStyle: "text-green-600"
    },
    {
      label: "Supprimer",
      icon: "trash",
      onClick: (item: TableItem) => alert("Supprimer " + item.nom),
      //classStyle: "text-red-600"
    }
  ];

  const filteredData = computed(() =>
    tableData.value.filter(item => {
      const matchesSearch = item.nom.toLowerCase().includes(search.value.toLowerCase());
      const matchesFilter = activeFilter.value === "all" ||
        (activeFilter.value === "late" && item.statut === "En retard") ||
        (activeFilter.value === "archive" && item.statut === "Archive");
      return matchesSearch && matchesFilter;
    })
  );

  function getStatusColor(statut: string) {
    if (statut === "Actif") return "green";
    if (statut === "En retard") return "red";
    if (statut === "Archive") return "default";
    return "default";
  }

  function handleAddSociete() {
    formRef.value?.validate().then(() => {
      tableData.value.push({ ...newSociete.value, id: nextId++ });
      showModal.value = false;
      newSociete.value = { id: 0, nom: "", secteur: "", employes: 0, statut: "Actif", cotisation: "" };
    }).catch(() => { });
  }




  const formSociete = ref<any>(null)

  function openForm() {
    if (formSociete.value) {
      formSociete.value.open()
    }
  }

  // Handle checkbox selection
  function handleSelectionChange(keys: (string | number)[]) {
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
