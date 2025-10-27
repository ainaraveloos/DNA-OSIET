<template>
  <div class="p-4">
    <!-- HEADER avec recherche et zone d'actions -->
    <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      <!-- Filtres -->
      <div class="flex flex-row items-center justify-between w-full lg:max-w-78">
        <FilterButtons />
      </div>

      <!-- Zone recherche + actions -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 w-full lg:w-auto">
        <a-input-search v-model:value="search" placeholder="Rechercher..." allow-clear
          class="w-full sm:w-2/3 md:w-1/2 lg:w-72" enter-button @search="handleSearch" />
        <div class="flex flex-row gap-2 justify-end">
          <Bouton @click="openForm" label="Ajouter" icon="plus"
            class="flex flex-row w-full items-center justify-center" />
          <slot name="actionsHeader" />
        </div>
      </div>
    </div>

    <!-- ====== MODE CARTES (sm/md/lg) : AUCUNE INFO PERDUE ====== -->
    <div class="xl:hidden">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
        <a-card v-for="record in data.data" :key="getRowKey(record)" class="rounded-2xl shadow-sm"
          :body-style="{ padding: '12px' }">
          <!-- Bandeau haut : images si présentes -->
          <div class="flex items-center gap-3 mb-3">
            <!-- Image principale -->
            <div v-if="hasColumnKey('image')" class="shrink-0">
              <a-image :width="64" :src="record.thumb_image ?? record.thumb_img"
                :preview="{ src: record.background_image ?? record.img }" fallback=""
                class="rounded-lg object-cover h-16 w-16" />
            </div>
            <!-- Deuxième image -->
            <div v-if="hasColumnKey('img_2')" class="shrink-0">
              <a-image :width="64" :src="record.thumb_img2 ?? record.thumb_img2" :preview="{ src: record.img_2 }"
                fallback="" class="rounded-lg object-cover h-16 w-16" />
            </div>

            <!-- Actions (menu ellipsis ou boutons inline) -->
            <div class="ml-auto flex items-center">
              <template v-if="btn_action">
                <a-dropdown placement="bottomRight" :trigger="['click']">
                  <a-button type="text" class="hover:!bg-transparent hover:!text-gray-700 hover:shadow-none !text-lg">
                    <font-awesome-icon icon="fa-solid fa-ellipsis" class="text-gray-700" />
                  </a-button>
                  <template #overlay>
                    <a-menu>
                      <span v-for="action in actions" :key="action.text">
                        <hr v-if="action.text === '-'" />
                        <a-menu-item v-else :class="action.class" :disabled="handleActionDisabled(action, record)"
                          @click="action.action(record)" v-if="handleActionVisible(action, record)">
                          <font-awesome-icon v-if="action.icon" class="me-2 text-gray-600" :icon="action.icon" />
                          <span class="text-gray-700">{{ action.text }}</span>
                        </a-menu-item>
                      </span>
                    </a-menu>
                  </template>
                </a-dropdown>
              </template>
              <template v-else>
                <div class="flex gap-2">
                  <a-button v-for="action in actions" :key="action.text" :class="action.class"
                    :disabled="handleActionDisabled(action, record)" @click="action.action(record)" type="text"
                    class="!p-0 !h-0 hover:!bg-transparent hover:!text-gray-700 hover:shadow-none !text-lg"
                    :title="action.text" v-if="handleActionVisible(action, record)">
                    <font-awesome-icon :icon="action.icon" class="text-gray-700" />
                  </a-button>
                </div>
              </template>
            </div>
          </div>

          <!-- Grille de DÉTAILS : affiche TOUTES les colonnes sauf images et action -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
            <template v-for="col in detailColumns" :key="col.key || col.dataIndex">
              <div class="min-w-0">
                <div class="text-[11px] uppercase tracking-wide text-gray-500">
                  {{ getColumnTitle(col) }}
                </div>
                <div class="text-sm text-gray-800 whitespace-pre-wrap break-words">
                  <!-- reprend la logique du bodyCell (fallback) -->
                  <template v-if="col.key === 'image' || col.key === 'img_2' || col.key === 'action'">
                    <!-- ignoré ici, déjà géré au-dessus -->
                  </template>
                  <template v-else>
                    {{ formatValue(resolveValue(record, col)) }}
                  </template>
                </div>
              </div>
            </template>
          </div>

          <!-- Slot "expandedRowRender" si fourni -->
          <div v-if="$slots.expandedRowRender" class="mt-3">
            <a-collapse ghost>
              <a-collapse-panel key="more" header="Plus de détails">
                <slot name="expandedRowRender" :record="record" />
              </a-collapse-panel>
            </a-collapse>
          </div>
        </a-card>
      </div>

      <!-- Pagination (cartes) -->
      <div class="flex justify-center mt-4">
        <a-pagination :total="data.total" :current="data.current_page" :pageSize="data.per_page"
          :show-size-changer="false" @change="handleCardPageChange" />
      </div>
    </div>

    <!-- ====== MODE TABLEAU (xl) ====== -->
    <div class="hidden xl:block">
      <div class="overflow-x-auto">
        <a-table class="main-table whitespace-nowrap main-rounded overflow-hidden" :data-source="data.data"
          :pagination="pagination" @change="handleTableChange" :columns="columns" :row-key="rowKey"
          :scroll="{ x: true }" size="middle" bordered
          :row-class-name="(_record, index) => (index % 2 === 1 ? 'table-striped' : null)">
          <!-- HEADER -->
          <template #headerCell="{ column }">
            <span class="uppercase font-semibold text-gray-700 flex justify-center text-xs sm:text-sm md:text-base">
              {{ column.title }}
            </span>
          </template>

          <!-- Vide -->
          <template #emptyText>
            <a-empty description="Aucun résultat trouvé" class="my-10 text-gray-500" />
          </template>

          <!-- CELLULES -->
          <template #bodyCell="{ column, record, text }">
            <!-- Image principale -->
            <template v-if="column.key === 'image'">
              <div class="flex justify-center">
                <a-image :width="60" :src="record.thumb_image ?? record.thumb_img"
                  :preview="{ src: record.background_image ?? record.img }" fallback=""
                  class="rounded-lg object-cover h-[40px]" />
              </div>
            </template>

            <!-- Deuxième image -->
            <template v-else-if="column.key === 'img_2'">
              <div class="flex justify-center">
                <a-image :width="60" :src="record.thumb_img2 ?? record.thumb_img2" :preview="{ src: record.img_2 }"
                  fallback="" class="rounded-lg object-cover h-[40px]" />
              </div>
            </template>

            <!-- Actions -->
            <template v-else-if="column.key === 'action'">
              <div class="flex justify-center items-center">
                <a-dropdown placement="bottomRight" :trigger="['click']" v-if="btn_action">
                  <a-button type="text" class="hover:!bg-transparent hover:!text-gray-700 hover:shadow-none !text-lg">
                    <font-awesome-icon icon="fa-solid fa-ellipsis" class="text-gray-700" />
                  </a-button>
                  <template #overlay>
                    <a-menu>
                      <span v-for="action in actions" :key="action.text">
                        <hr v-if="action.text === '-'" />
                        <a-menu-item v-else :class="action.class" :disabled="handleActionDisabled(action, record)"
                          @click="action.action(record)" v-if="handleActionVisible(action, record)">
                          <font-awesome-icon v-if="action.icon" class="me-2 text-gray-600" :icon="action.icon" />
                          <span class="text-gray-700">{{ action.text }}</span>
                        </a-menu-item>
                      </span>
                    </a-menu>
                  </template>
                </a-dropdown>

                <!-- Boutons inline -->
                <template v-else v-for="action in actions" :key="action.text">
                  <a-button :class="action.class" :disabled="handleActionDisabled(action, record)"
                    @click="action.action(record)" type="text"
                    class="!p-0 !h-0 hover:!bg-transparent hover:!text-gray-700 hover:shadow-none !text-lg"
                    :title="action.text">
                    <font-awesome-icon :icon="action.icon" class="text-gray-700" />
                  </a-button>
                </template>
              </div>
            </template>

            <!-- Texte par défaut -->
            <template v-else>
              <div class="text-center text-gray-800 cursor-default text-xs sm:text-sm">
                {{ text }}
              </div>
            </template>
          </template>

          <!-- SUMMARY -->
          <template #summary>
            <slot name="summary" />
          </template>

          <!-- Lignes étendues -->
          <template v-if="$slots.expandedRowRender" #expandedRowRender="{ record }">
            <slot name="expandedRowRender" :record="record" />
          </template>
        </a-table>
      </div>
    </div>
    <FormSuivi ref="formSuivi" />
  </div>
</template>

<script setup>
import Bouton from "@/components/utils/Bouton.vue";
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import FormSuivi from "../../pages/societe/FormSuivi.vue";
import FilterButtons from "./FilterButtons.vue";


const formSuivi = ref(false)

function openForm() {
  formSuivi.value.visible = true
}


const props = defineProps({
  data: { type: Object, required: true },
  columns: { type: Array, required: true },
  actions: Array,
  filters: { type: Object, default: () => ({}) },
  rowKey: { type: [String, Function], default: 'id' },
  reload_route: String,
  showIndex: { type: Boolean, default: false },
  btn_action: { type: Boolean, default: true },
});

const page = usePage();
const search = ref(props.filters.search ?? '');

/* Pagination (tableau xl) */
const pagination = computed(() => ({
  total: props.data.total,
  current: props.data.current_page,
  pageSize: props.data.per_page,
  showSizeChanger: false,
  showTotal: (total, range) => `${range[0]} - ${range[1]} sur ${total}`,
}));

/* ----- Helpers responsive (cartes) ----- */
const filteredColumns = computed(() => props.columns ?? []);
const detailColumns = computed(() =>
  filteredColumns.value.filter(c => c?.key !== 'image' && c?.key !== 'img_2' && c?.key !== 'action')
);

const hasColumnKey = (k) => filteredColumns.value.some(c => c.key === k);

const getRowKey = (record) => {
  if (typeof props.rowKey === 'function') return props.rowKey(record);
  return record?.[props.rowKey ?? 'id'] ?? record?.id ?? JSON.stringify(record);
};

const getColumnTitle = (col) => {
  if (typeof col?.title === 'string') return col.title;
  // Si le titre est un VNode/slot, on fournit un label fallback
  return (col?.key || col?.dataIndex || '').toString().replaceAll('_', ' ').trim() || '—';
};

const resolveValue = (record, col) => {
  // Supporte dataIndex string, array path, sinon tente par key
  const di = col?.dataIndex;
  if (Array.isArray(di)) {
    return di.reduce((acc, k) => (acc ? acc[k] : undefined), record);
  }
  if (typeof di === 'string' && di.length) {
    return record?.[di];
  }
  if (col?.key && record?.[col.key] !== undefined) {
    return record[col.key];
  }
  return undefined;
};

const formatValue = (v) => {
  if (v === null || v === undefined || v === '') return '—';
  if (typeof v === 'boolean') return v ? 'Oui' : 'Non';
  if (typeof v === 'number') return String(v);
  if (typeof v === 'object') {
    try { return JSON.stringify(v); } catch { return '—'; }
  }
  return String(v);
};

/* ----- Navigation (Inertia) ----- */
// Recherche
const handleSearch = (value) => {
  router.get(
    page.url.split('?')[0],
    { ...props.filters, search: value, page: 1 },
    { preserveState: true, preserveScroll: true }
  );
};

// Pagination/table tri (tableau xl)
const handleTableChange = (pag /*, filters, sorter */) => {
  router.get(
    page.url.split('?')[0],
    { page: pag.current, ...props.filters, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};

// Pagination (cartes sm/md/lg)
const handleCardPageChange = (pageNum) => {
  router.get(
    page.url.split('?')[0],
    { page: pageNum, ...props.filters, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};

const handleActionVisible = (action, record) => {
  if (action.visible === null || action.visible === undefined) return true;
  if (typeof action.visible === 'boolean') return action.visible;
  if (typeof action.visible === 'function') return action.visible(record);
  return false;
};

const handleActionDisabled = (action, record) => {
  if (action.disabled === null || action.disabled === undefined) return false;
  if (typeof action.disabled === 'boolean') return action.disabled;
  if (typeof action.disabled === 'function') return action.disabled(record);
  return true;
};

onMounted(() => {
  // Supprimer colonne index si présente
  const i = props.columns.findIndex((c) => c.key === 'index');
  if (i !== -1) props.columns.splice(i, 1);

  // Injecter colonne d'actions si absente
  if (props.actions && !props.columns.find((c) => c.key === 'action')) {
    props.columns.push({
      title: 'Actions',
      key: 'action',
      align: 'center',
      customCell: () => ({ class: 'fixed-column' }),
    });
  }
});
</script>

<style scoped>
/* Zébrage lignes (tableau xl) */
:deep(.table-striped) td {
  background-color: rgba(0, 0, 0, 0.02);
}

/* Neutraliser styles de lien natifs AntD dans le tableau */
:deep(.ant-table a) {
  color: inherit !important;
  text-decoration: none !important;
}

:deep(.ant-table a:hover) {
  color: inherit !important;
}

/* Coins arrondis du conteneur tableau */
.main-rounded {
  border-radius: 0.75rem;
}
</style>
