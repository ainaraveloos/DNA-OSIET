<template>
    <BaseModal
        v-model:open="visible"
        :titre="titre"
        ok-text="Enregistrer"
        :loading="loading"
        size="md"
        :show-footer="true"
        @submit="handleSubmit"
        @close="handleClose"
    >
        <FormItem label="Nom de la Société">
            <FormInput
                v-model="form.nom"
                variant="input"
                size="large"
                placeholder="Entrez le nom"
                :disabled="isViewMode"
            />
        </FormItem>

        <FormItem label="Secteur d'Activité" :help="form.errors.secteur">
            <FormInput
                v-model="form.secteur"
                variant="password"
                size="large"
                placeholder="Secteur"
            />
        </FormItem>

        <FormItem label="Nombre d'Employés">
            <FormInput
                v-model="form.employes"
                variant="number"
                size="large"
                :disabled="isViewMode"
                :min="8"
            />
        </FormItem>

        <FormItem label="Statut">
            <a-select
                v-model:value="form.statut"
                size="large"
                placeholder="Choisir un statut"
                :disabled="isViewMode"
            >
                <a-select-option value="Actif">Actif</a-select-option>
                <a-select-option value="En retard">En retard</a-select-option>
                <a-select-option value="Archive">Archive</a-select-option>
            </a-select>
        </FormItem>

        <FormItem label="Dernière Cotisation">
            <a-date-picker
                v-model:value="form.cotisation"
                size="large"
                class="w-full"
                format="DD/MM/YYYY"
                :disabled="isViewMode"
            />
        </FormItem>
    </BaseModal>
</template>

<script setup>
import BaseModal from '@/components/BaseModal/BaseModal.vue'
import FormInput from '@/components/Formulaire/FormInput.vue'
import FormItem from '@/components/Formulaire/FormItem.vue'
import { useForm } from '@inertiajs/vue3'
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

const form = useForm({
    nom: props.initialData.nom || '',
    secteur: props.initialData.secteur || '',
    employes: props.initialData.employes || null,
    statut: props.initialData.statut || 'Actif',
    cotisation: props.initialData.cotisation || null
})



const titre = computed(() => {
    return props.initialData.id ? 'Modifier la société' : 'Ajouter une société'
})

async function handleSubmit() {
console.log(form.value);
}

function openModal() {
    visible.value = true
}
function handleClose() {
    visible.value = false
    emit('close')
}

// Exposer les méthodes pour le parent
defineExpose({
    visible,
    open: openModal,
    close: handleClose
})
</script>


