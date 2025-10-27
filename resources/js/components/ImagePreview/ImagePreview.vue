<template>
    <div class="relative group">
        <a-image
            :src="thumbnailUrl"
            :alt="alt"
            :width="40"
            :height="40"
            :preview="true"
            class="image-preview-thumbnail"
            :preview-mask="true"
            :preview-src="imageUrl"
        />
    </div>
</template>

<script setup>
const props = defineProps({
    thumbnailUrl: {
        type: String,
        required: true,
    },
    imageUrl: {
        type: String,
        required: true,
    },
    alt: {
        type: String,
        default: "Image preview",
    },
});
</script>

<style scoped>
.image-preview-thumbnail {
    border-radius: 8px;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.image-preview-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #1890ff;
}

/* Styles pour le preview */
:deep(.ant-image-preview) {
    background: rgba(0, 0, 0, 0.9) !important;
}

:deep(.ant-image-preview-content) {
    background: transparent !important;
}

:deep(.ant-image-preview-img) {
    max-width: 60vw !important;
    max-height: 60vh !important;
}
</style>
