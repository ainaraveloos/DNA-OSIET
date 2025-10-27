<template>
    <BaseModal
        v-model:open="visible"
        :titre="titre"
        ok-text="Enregistrer"
        :loading="loading"
        size="lg"
        :show-footer="true"
        @submit="handleSubmit"
        @close="handleClose"
    >
        <div class="p-6">
            <a-row :gutter="[24, 24]">
                <!-- Section Upload d'image -->
                <a-col :xs="24" :lg="6" class="flex justify-center">
                    <div class="w-full max-w-xs">
                        <ImageUpload
                            :url="imgPreview"
                            @onChange="handlePhotoUpload"
                            @onRemove="handlePhotoRemove"
                            class="mx-auto"
                        />
                        <!-- Info formats -->
                        <div class="mt-3 p-2 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-xs text-blue-600 font-medium text-center">
                                <font-awesome-icon icon="fa-solid fa-info-circle" class="mr-1"/>
                                JPG, PNG acceptés
                            </p>
                        </div>
                    </div>
                </a-col>

                <!-- Section Formulaire -->
                <a-col :xs="24" :lg="18">
                    <div class="space-y-6">
                        <!-- Ligne 1: Nom de la société -->
                        <FormItem
                            label="Nom de la Société"
                            required
                            :help="form.errors.name"
                        >
                            <FormInput
                                v-model="form.name"
                                variant="input"
                                size="large"
                                placeholder="Entrez le nom de la société"
                                :disabled="isViewMode"
                            />
                        </FormItem>

                        <!-- Ligne 2: Nom du contact -->
                        <FormItem
                            label="Nom du Contact"
                            :help="form.errors.nom_contact"
                        >
                            <FormInput
                                v-model="form.nom_contact"
                                variant="input"
                                size="large"
                                placeholder="Nom du contact"
                                :disabled="isViewMode"
                            />
                        </FormItem>
                        <!-- Ligne 4: Email + Téléphone -->
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <FormItem
                                    label="Email"
                                    :help="form.errors.email"
                                >
                                    <FormInput
                                        v-model="form.email"
                                        variant="input"
                                        size="large"
                                        type="email"
                                        placeholder="Adresse email"
                                        :disabled="isViewMode"
                                    />
                                </FormItem>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <FormItem
                                    label="Téléphone"
                                    :help="form.errors.telephone"
                                >
                                    <FormInput
                                        v-model="form.telephone"
                                        variant="input"
                                        size="large"
                                        placeholder="Numéro de téléphone"
                                        :disabled="isViewMode"
                                    />
                                </FormItem>
                            </a-col>
                        </a-row>

                        <!-- Ligne 4: Adresse -->
                        <FormItem
                            label="Adresse"
                            :help="form.errors.adresse"
                        >
                            <FormInput
                                v-model="form.adresse"
                                variant="input"
                                size="large"
                                placeholder="Adresse complète de la société"
                                :disabled="isViewMode"
                            />
                        </FormItem>

                        <!-- Ligne 6: NIF + STAT -->
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="12">
                                <FormItem
                                    label="NIF"
                                    :help="form.errors.nif"
                                >
                                    <FormInput
                                        v-model="form.nif"
                                        variant="input"
                                        size="large"
                                        placeholder="Numéro d'identification fiscale"
                                        :disabled="isViewMode"
                                    />
                                </FormItem>
                            </a-col>
                            <a-col :xs="24" :sm="12">
                                <FormItem
                                    label="STAT"
                                    :help="form.errors.stat"
                                >
                                    <FormInput
                                        v-model="form.stat"
                                        variant="input"
                                        size="large"
                                        placeholder="Numéro statistique"
                                        :disabled="isViewMode"
                                    />
                                </FormItem>
                            </a-col>
                        </a-row>

                        <!-- Ligne 7: Statut -->
                        <FormItem
                            label="Statut"
                            :help="form.errors.status"
                        >
                            <a-select
                                v-model:value="form.status"
                                size="large"
                                placeholder="Sélectionner le statut"
                                class="w-full"
                                :disabled="isViewMode"
                            >
                                <a-select-option value="actif">Actif</a-select-option>
                                <a-select-option value="inactif">Inactif</a-select-option>
                            </a-select>
                        </FormItem>
                    </div>
                </a-col>
            </a-row>
        </div>
    </BaseModal>
</template>

<script setup>
import BaseModal from '@/components/BaseModal/BaseModal.vue'
import FormInput from '@/components/Formulaire/FormInput.vue'
import FormItem from '@/components/Formulaire/FormItem.vue'
import ImageUpload from '@/components/ImageUpload/ImageUpload.vue'
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['submit', 'close'])

const visible = ref(false)
const loading = ref(false)
const imgPreview = ref(null)

const form = useForm({
    id: props.initialData.id || null,
    name: props.initialData.name || '',
    nom_contact: props.initialData.nom_contact || '',
    nif: props.initialData.nif || '',
    stat: props.initialData.stat || '',
    adresse: props.initialData.adresse || '',
    telephone: props.initialData.telephone || '',
    email: props.initialData.email || '',
    status: props.initialData.status || 'actif',
    img: props.initialData.img || null
})


const titre = computed(() => {
    return props.initialData.id ? 'Modifier la société' : 'Ajouter une société'
})

// Gestion de l'upload d'image
const handlePhotoUpload = (file) => {
    form.img = file

}

// Gestion de la suppression d'image
const handlePhotoRemove = () => {
    form.img = null
    imgPreview.value = null
}

//Nouveau Societe
function addNew() {
    visible.value = true
    // Reset du formulaire pour un nouvel ajout
    form.reset()
    form.clearErrors()
    form.id = null
    imgPreview.value = null
}

// Fonction pour modifier une société
const update = (rowData) => {
    router.visit(`${route('societe.show', {societe: rowData.id})}`, {
        preserveState: true,
        preserveScroll: true,
        only: ['flash'],
        onSuccess: (response) => {
            const responseData = response.props.flash.data
            Object.keys(responseData).forEach(key => {
                if (form.hasOwnProperty(key)) { form[key] = responseData[key] }
            })

            // Mettre à jour l'image preview si une image existe
            if (responseData.image_url) { imgPreview.value = responseData.image_url }
            else { imgPreview.value = null }

            visible.value = true
        },
    })
}

async function handleSubmit() {
    loading.value = true
    form.clearErrors()

    try {
        const method = form.id ? 'put' : 'post'
        const url = form.id ? route('societe.update', form.id) : route('societe.store')

        form.transform(data => ({ ...data,  _method: method.toUpperCase()})).post(url, {
            onSuccess: () => { handleClose() },
            onError: (errors) => { form.errors = errors},
            forceFormData: true
        })

    } catch (error) { console.error('Erreur lors de la soumission:', error) }
    finally { loading.value = false }
}

function handleClose() {
    visible.value = false
    imgPreview.value = null
    form.reset()
    form.clearErrors()
    emit('close')
}

// Exposer les méthodes pour le parent
defineExpose({
    visible,
    add: addNew,
    update,
    close: handleClose
})
</script>

<style scoped>
/* Styles pour les transitions et animations */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Amélioration des inputs */
.modern-input {
    @apply transition-all duration-300;
}


</style>
