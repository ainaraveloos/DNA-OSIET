<template>
    <a-card
        id="image-upload"
        class="!rounded-none overflow-hidden !border !border-dashed !border-primary transition-all
                duration-300 hover:shadow-md bg-gradient-to-br from-gray-50 to-white"
        :class="{
        'border-primary shadow-md': image,
        'hover:bg-gradient-to-br hover:from-blue-50 hover:to-gray-50': !image
      }"
        style="min-width: 200px;"
    >

        <template #cover>
            <div class="relative p-2">
                <!-- Image avec overlay moderne -->
                <div class="relative flex justify-center h-48 group">
                    <div class="relative">
                        <a-avatar
                            :size="224"
                            class="!object-contain transition-all duration-300"
                            :src="image || '/img/placeholder_img.png'"
                            shape="square"
                        />

                        <!-- Overlay avec bouton supprimer - positionné directement sur l'avatar -->
                        <div
                            v-if="showRemoveButton && image && image !== '/img/placeholder_img.png'"
                            class="absolute inset-0 flex items-center justify-center bg-white/15 bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 rounded-lg opacity-0 group-hover:opacity-100"
                            style="width: 224px; height: 224px;"
                        >
                            <a-tooltip placement="top" title="Supprimer l'image">
                                <a-button
                                    danger
                                    type="primary"
                                    shape="circle"
                                    size="large"
                                    class="shadow-lg  transition-all duration-500 transform hover:scale-110"
                                    @click="onRemove"
                                >
                                    <font-awesome-icon
                                        icon="fa-solid fa-trash"
                                        class="text-lg"
                                    />
                                </a-button>
                            </a-tooltip>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template #actions>
            <div class="px-3 py-3 relative flex justify-center">
                <!-- Bouton Upload -->
                <a-upload
                    :multiple="false"
                    accept="image/png, image/jpeg, image/svg+xml"
                    list-type="picture"
                    :maxCount="1"
                    :show-upload-list="false"
                    :before-upload="beforeUpload"
                    class="!w-full !mx-auto"
                >
                    <a-tooltip placement="top" :title="image ? 'Changer l\'image' : 'Choisir une image'">
                        <a-button
                            type="primary"
                            size="middle"
                            shape="default"
                            class="p-2 flex items-center justify-center w-9 h-9 border border-gray-300 rounded-md bg-gradient-to-r from-sky-500 to-primary hover:from-primary hover:to-sky-500 font-semibold shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex items-center justify-center gap-3">
                                <font-awesome-icon
                                    icon="fa-solid fa-camera"
                                    class="text-lg"
                                />
                            </div>
                        </a-button>
                    </a-tooltip>
                </a-upload>
            </div>
        </template>
    </a-card>
</template>

<script setup>

import { defineEmits, ref, watch } from "vue";

const emit = defineEmits(['onChange', 'onRemove'])
const props = defineProps({
    url: {
        type: String,
        default: ''
    },
    size: {
        type: Number,
        default: 100
    },
    color: {
        type: String,
        default: 'gray'
    },
    icon: {
        logo: String,
        default: 'fa-circle-dot'
    },
    showRemoveButton: {
        type: Boolean,
        default: true
    }
})

const image = ref(props.url ?? '')
const currentFile = ref(null)

watch(() => props.url, (newValue) => {
    image.value = newValue ?? ''
    // Si l'URL change, on reset le fichier courant
    if (!newValue) {
        currentFile.value = null
    }
}, { immediate: true })

function getBase64(img, callback) {
    const reader = new FileReader();
    reader.addEventListener('load', () => callback(reader.result));
    reader.readAsDataURL(img);
}

const beforeUpload = file => {
    // Validation du fichier
    const isValidType = file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/svg+xml'
    if (!isValidType) {
        console.error('Format de fichier non supporté. Utilisez JPG, PNG ou SVG.')
        return false
    }

    const isValidSize = file.size / 1024 / 1024 < 5 // 5MB max
    if (!isValidSize) {
        console.error('Fichier trop volumineux. Taille maximale: 5MB')
        return false
    }

    getBase64(file, (base64) => {
        image.value = base64
        currentFile.value = file
        emit('onChange', file)
    });
    return false;
};

const onRemove = () => {
    image.value = '/img/placeholder_img.png'
    currentFile.value = null
    emit('onChange', null)
    emit('onRemove')
}

</script>

<style>

#image-upload {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

#image-upload .ant-card-body {
    padding: 0px !important;
}

#image-upload .ant-card-actions {
    @apply border-t-0 bg-transparent;
}

#image-upload .ant-card-actions li {
   margin: 0px !important;
}

#image-upload .ant-card-actions li > span {
    @apply cursor-default w-full;
}

/* Animation pour l'icône upload */
@keyframes bounce-gentle {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-4px);
    }
}

.animate-bounce {
    animation: bounce-gentle 2s infinite;
}

/* Effet glassmorphism */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
}

/* Amélioration des transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Amélioration de l'avatar */
.ant-avatar {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.group:hover .ant-avatar {
    filter: brightness(0.8);
}
</style>
