<template>
    <a-modal
        v-model:open="open"
        @close="emits('close')"
        class="main-modal rounded-none"
        :wrap-class-name="wrapClassName"
        @cancel="emits('close')"
        @ok="emits('submit')"
        :ok-text="okText"
        cancel-text="Fermer"
        :confirm-loading="loading"
        :mask-closable="true"
        :closable="!loading"
        :cancel-button-props="{ disabled: loading }"
        :width="modalWidths[size]"
        :footer="showFooter ? undefined : null"
        :style="size === 'full_screen' ? { top: 0, paddingBottom: 0, margin: 0 } : {}"
        :body-style="size === 'full_screen' ? { height: 'calc(100vh - 120px)', overflow: 'auto' } : {}"
    >
        <template #title>
            <div class="modal-header text-white">
                <slot name="title">
                    {{ titre }}
                </slot>
            </div>
        </template>
        <a-alert
            type="warning"
            class="border-none my-7 py-3"
            v-if="show_champ_obligatoir"
        >
            <template #message>
                Les champs marqués par <b class="text-red-600">*</b> sont
                obligatoires.
            </template>
        </a-alert>

        <a-form layout="vertical">
            <slot />
        </a-form>
    </a-modal>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    loading: {
        type: Boolean,
        default: false,
    },
    okText: {
        type: String,
        default: () => "Enrégistrer",
    },
    show: {
        type: Boolean,
        default: () => true,
    },
    show_champ_obligatoir: {
        type: Boolean,
        require: false,
        default: () => false,
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "full_screen"].includes(value),
    },
    titre: {
        type: String,
        required: true,
    },
    showFooter: {
        type: Boolean,
        default: true,
        required: false,
    },
});

const open = defineModel("open");
const emits = defineEmits(["close", "submit"]);

const modalWidths = {
    sm: "600px",
    md: "800px",
    lg: "1000px",
    full_screen: "100%",
};

// Always apply primary-modal; add full-modal when size is full_screen
const wrapClassName = computed(() =>
    props.size === 'full_screen' ? 'primary-modal full-modal' : 'primary-modal'
);
</script>



<style >


/* Styles globaux  pour le full-screen */
.full-modal .ant-modal {
    max-width: 100vw !important;
    max-height: 100vh !important;
    width: 100vw !important;
    height: 100vh !important;
    top: 0 !important;
    left: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
}
/* Styles globaux pour le header */
.primary-modal .ant-modal-header {
    margin: -24px -24px 0px -24px !important;
    padding: 24px !important;
    background: rgb(70, 166, 99) !important;
}
.primary-modal .ant-modal-header .ant-modal-title {
    color: #ffffff !important;
    margin: 0 !important;
    font-weight: 600 !important;
}
.primary-modal .ant-modal-close {
    color: #ffffff !important;
    font-size: 16px !important;
}
.primary-modal .ant-modal-close:hover {
    color: #f0f0f0 !important;
}
/* Styles spécifiques pour full-screen */
.full-modal .ant-modal-header {
    margin: -24px -24px 0px -24px !important;
    padding: 24px !important;
    background: rgb(70, 166, 99) !important;
}

.full-modal .ant-modal-content {
    display: flex !important;
    flex-direction: column !important;
    height: 100vh !important;
    max-height: 100vh !important;
}

/* Styles globaux pour le body */
.primary-modal .ant-modal-content {
    display: flex !important;
    flex-direction: column !important;
}
.primary-modal .ant-modal-body {
    flex: 1 1 auto !important;
    overflow: auto !important;
}

/* Styles globaux pour le footer */
.primary-modal .ant-modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 1;
    padding: 16px 24px;
    margin: 0 -24px -16px -24px;
}
.full-modal .ant-modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 1;
    padding: 16px 24px;
    margin: 0 -24px -16px -24px;
}


.full-modal .ant-modal-body {
    flex: 1 1 auto !important;
    overflow: auto !important;
    max-height: calc(100vh - 120px) !important;
}

</style>
