<script setup lang="ts">
import { MoreOutlined } from '@ant-design/icons-vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

// Types
interface Action<T> {
    label: string;
    icon?: any;
    onClick: (record: T) => void;
    visible?: (record: T) => boolean;
    disabled?: (record: T) => boolean;
    classStyle?: string;
    privilege?: string | (() => string);
}

interface Column<T> {
    key: string;
    label: string;
    align?: 'left' | 'center' | 'right';
    width?: number;
    render?: (record: T, index: number) => any;
}

interface DataTableProps<T> {
    data: {
        data: T[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    columns: Column<T>[];
    actions?: Action<T>[];
    filters?: Record<string, any>;
    endpoint?: string;
    actionOnDropdown?: boolean;
    selectableRows?: boolean;
    selectedIds?: (string | number)[];
    onSelectionChange?: (keys: (string | number)[]) => void;
}

// Props
const props = withDefaults(defineProps<DataTableProps<any>>(), {
    actions: () => [],
    filters: () => ({}),
    actionOnDropdown: true,
    selectableRows: false,
    selectedIds: () => [],
});

// Computed
const startIndex = computed(() => (props.data.current_page - 1) * props.data.per_page);

const antdColumns = computed(() => {
    const columns = props.columns.map((col) => {
        const colAlign = col.align ?? 'left';
        const colWidth = col.width ?? undefined;

        if (col.key === 'index') {
            return {
                title: col.label.toUpperCase(),
                dataIndex: '_index',
                key: '_index',
                width: colWidth,
                align: colAlign,
                ellipsis: true,
            };
        }

        return {
            title: col.label.toUpperCase(),
            dataIndex: col.key,
            key: col.key,
            align: colAlign,
            width: colWidth,
            ellipsis: true,
            render: col.render,
        } as any;
    });

    // Add actions column if actions exist
    if (visibleActions.length > 0) {
        columns.push({
            title: '',
            key: 'actions',
            align: 'right',
            width: props.actionOnDropdown ? 50 : 100,
            fixed: 'right',
            ellipsis: false,
        } as any);
    }

    return columns;
});

const paginationPages = computed(() => {
    const currentPage = props.data.current_page;
    const lastPage = props.data.last_page;
    const pages: (number | string)[] = [];

    if (lastPage <= 5) {
        for (let i = 1; i <= lastPage; i++) pages.push(i);
    } else {
        pages.push(1);
        if (currentPage > 3) pages.push('...');
        const start = Math.max(2, currentPage - 1);
        const end = Math.min(lastPage - 1, currentPage + 1);
        for (let i = start; i <= end; i++) pages.push(i);
        if (currentPage < lastPage - 2) pages.push('...');
        pages.push(lastPage);
    }

    return pages;
});

const rowSelection = computed(() => {
    if (!props.selectableRows) return undefined;

    return {
        selectedRowKeys: props.selectedIds,
        onChange: (keys: (string | number)[]) => {
            props.onSelectionChange?.(keys);
        },
    };
});

// Methods
const handlePageChange = (pageNum: number) => {
    if (props.endpoint) {
        router.get(
            props.endpoint,
            { page: pageNum, ...props.filters },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

const isActionVisible = (action: Action<any>, row: any) => {
    // Simplified visibility check without permissions for now
    if (action.visible !== undefined) return action.visible(row);
    return true;
};

const isActionDisabled = (action: Action<any>, row: any) => {
    return action.disabled ? action.disabled(row) : false;
};

const visibleActions = (record: any): Action<any>[] => {
    return props.actions.filter((action) => isActionVisible(action, record));
};
</script>

<style scoped>
.custom-table {
    width: 100%;
}
.custom-table :deep(.ant-table-header) {
    border-radius: 0 !important;
}
.custom-table-pagination {
    background-color: white;
    border-top: 1px solid #e5e7eb;
    border-bottom-left-radius: 16px !important;
    border-bottom-right-radius: 16px !important;
}

/* Supprimer border-radius entête Ant table */
.custom-table :deep(.ant-table-thead > tr:first-child > th:first-child) {
    border-top-left-radius: 0 !important;
    border-top-right-radius: 0 !important;
}
.custom-table :deep(.ant-table-thead > tr:first-child > th:last-child) {
    border-top-right-radius: 0 !important;
}

.custom-table :deep(.ant-table-thead > tr > th) {
    padding: 8px !important;
    background-color: rgb(70, 166, 99) !important;
    color: #ffffff !important;
    white-space: nowrap !important;
}
.custom-table :deep(.ant-table-tbody > tr > td) {
    border-bottom: 1px solid #f5f5f5 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

:deep(.ant-table-tbody > tr:hover > td) {
    background-color: #f8fafc !important;
}

:deep(.ant-table-tbody > tr:last-child > td) {
    border-bottom: none !important;
}

/* Forcer le respect des largeurs spécifiques */
.custom-table :deep(.ant-table-thead > tr > th),
:deep(.ant-table-tbody > tr > td) {
    table-layout: fixed !important;
}

.custom-table :deep(.ant-table) {
    table-layout: fixed !important;
    width: 100% !important;
}

/*voir les largeurs colonnes */
.custom-table :deep(.ant-table-thead > tr > th) {
    position: relative;
}
/* taille du checkbox */
.custom-table :deep(.ant-checkbox-inner) {
    width: 14px;
    height: 14px;
}

</style>

<template>
    <div class="space-y-6">
        <div class="overflow-x-auto">
            <a-table
                :data-source="data.data"
                :columns="antdColumns"
                :pagination="false"
                :row-key="(record: any) => record.id"
                sticky
                :scroll="{ x: 'max-content', y:400 }"
                :row-selection="rowSelection"
                :table-layout="'fixed'"
                class="custom-table"
            >
                <!-- Actions column template -->
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'actions'">
                        <div v-if="visibleActions(record).length > 0" class="flex justify-end">
                            <!-- Dropdown mode -->
                            <a-dropdown v-if="actionOnDropdown && visibleActions.length > 0" :trigger="['click']" class="!text-center">
                                <a-button size="small" >
                                    <template #icon>
                                        <MoreOutlined />
                                    </template>
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item
                                            v-for="(action, index) in visibleActions(record)"
                                            :key="index"
                                            :disabled="isActionDisabled(action, record)"
                                            @click="action.onClick(record)"
                                        >
                                            <span :class="action.classStyle ?? ''">
                                                <FontAwesomeIcon v-if="action.icon" :icon="action.icon" class="mr-2" />
                                                {{ action.label }}
                                            </span>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>

                            <!-- Button mode -->
                            <div v-else class="flex justify-end gap-2">
                                <span
                                    v-for="(action, index) in visibleActions(record)"
                                    :key="index"
                                    :class="`${action.classStyle ?? ''} cursor-pointer transition-colors`"
                                    @click="action.onClick(record)"
                                >
                                    <FontAwesomeIcon v-if="action.icon" :icon="action.icon" />
                                </span>
                            </div>
                        </div>
                    </template>

                    <!-- Index column template -->
                    <template v-else-if="column.key === '_index'">
                        {{ startIndex + record._index + 1 }}
                    </template>

                    <!-- Custom render template -->
                    <template v-else-if="column.render">
                        <component :is="() => column.render(record, record._index)" />
                    </template>

                    <!-- Default template -->
                    <template v-else>
                        {{ record[column.dataIndex] ?? '' }}
                    </template>
                </template>
            </a-table>

            <!-- Custom Pagination -->
            <div class="custom-table-pagination px-6 py-2">
                <div class="flex items-center justify-end gap-4 text-sm text-gray-600">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500">Page</span>
                        <span class="rounded-md bg-blue-50 px-2 py-1 font-semibold text-gray-900">{{ data.current_page }}</span>
                        <span class="text-gray-500">sur</span>
                        <span class="font-semibold text-gray-700">{{ data.last_page }}</span>
                        <span class="mx-2 text-gray-400">—</span>
                        <span class="font-medium text-gray-900">{{ data.total }}</span>
                        <span class="text-gray-500">enregistrements</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a-button :disabled="data.current_page === 1" @click="handlePageChange(data.current_page - 1)"> Précédent </a-button>

                        <template v-for="(page, idx) in paginationPages" :key="idx">
                            <a-button
                                v-if="typeof page === 'number'"
                                :type="page === data.current_page ? 'primary' : 'default'"
                                @click="handlePageChange(page)"
                                size="middle"
                            >
                                {{ page }}
                            </a-button>
                            <span v-else class="px-2">...</span>
                        </template>

                        <a-button :disabled="data.current_page === data.last_page" @click="handlePageChange(data.current_page + 1)">
                            Suivant
                        </a-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
