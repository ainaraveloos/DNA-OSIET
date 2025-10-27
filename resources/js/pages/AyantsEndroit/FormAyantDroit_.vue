<template>
    <BaseModal
        v-model:open="visible"
        :titre="titre"
        ok-text="Enregistrer"
        :loading="loading"
        size="full_screen"
        :show-footer="true"
        @submit="handleSubmit"
        @close="handleClose"
    >
        <div class="p-6">
            <a-tabs v-model:activeKey="activeTab" type="card">
                <!-- Tab 1: Identités -->
                <a-tab-pane key="identites" tab="Informations Personnelles">
                    <div class="pt-4">
                        <div class="space-y-4">
                            <!-- Nom + Prénom -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Nom" required :help="form.errors.nom">
                                        <FormInput  v-model="form.nom" variant="input"  size="large" placeholder="Nom"/>
                                    </FormItem>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Prénom" required :help="form.errors.prenom">
                                        <FormInput  v-model="form.prenom" variant="input" size="large" placeholder="Prénom"/>
                                    </FormItem>
                                </a-col>
                            </a-row>

                            <!-- Sexe + Date de naissance -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Sexe" :help="form.errors.sexe">
                                        <a-select v-model:value="form.sexe" size="large" placeholder="Sélectionner" class="w-full">
                                            <a-select-option value="M">MASCULIN</a-select-option>
                                            <a-select-option value="F">FEMININ</a-select-option>
                                        </a-select>
                                    </FormItem>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Date de naissance" :help="form.errors.date_naissance">
                                        <a-date-picker -model:value="form.date_naissance" size="large" placeholder="Date de naissance" class="w-full" value-format="YYYY-MM-DD" />
                                    </FormItem>
                                </a-col>
                            </a-row>

                            <!-- Adresse -->
                            <FormItem label="Adresse" :help="form.errors.adresse">
                                <FormInput v-model="form.adresse" variant="input" size="large" placeholder="Adresse complète" />
                            </FormItem>

                            <!-- Téléphone + Email -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Téléphone" :help="form.errors.telephone">
                                        <FormInput v-model="form.telephone" variant="input" size="large" placeholder="Téléphone" />
                                    </FormItem>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Email" :help="form.errors.email">
                                        <FormInput v-model="form.email" variant="input" size="large" type="email" placeholder="Email" />
                                    </FormItem>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </a-tab-pane>

                <!-- Tab 2: Professionnels -->
                <a-tab-pane key="professionnels" tab="Informations Professionnelles">
                    <div class="pt-4">
                        <div class="space-y-4">
                            <!-- Société -->
                            <FormItem label="Société" :help="form.errors.societe">
                                <FormInput v-model="form.societe" variant="input" size="large" placeholder="Nom de la société" />
                            </FormItem>

                            <!-- Date d'embauche + Poste -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Date d'embauche" :help="form.errors.date_debut">
                                        <a-date-picker v-model:value="form.date_debut" size="large" placeholder="Date" class="w-full" value-format="YYYY-MM-DD" />
                                    </FormItem>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Poste" :help="form.errors.poste">
                                        <FormInput v-model="form.poste" variant="input" size="large" placeholder="Poste" />
                                    </FormItem>
                                </a-col>
                            </a-row>

                            <!-- Statut + Date fin contrat -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Statut d'emploi" :help="form.errors.status">
                                        <a-select v-model:value="form.status" size="large" placeholder="Statut" class="w-full" @change="handleStatusChange" >
                                            <a-select-option value="permanent">Permanent</a-select-option>
                                            <a-select-option value="cdd">CDD</a-select-option>
                                            <a-select-option value="temporaire">Temporaire</a-select-option>
                                        </a-select>
                                    </FormItem>
                                </a-col>
                                <a-col :xs="24" :sm="12">
                                    <FormItem label="Date fin contrat" :required="form.status !== 'permanent'" :help="form.errors.date_fin" >
                                        <a-date-picker v-model:value="form.date_fin" size="large" placeholder="Date" value-format="YYYY-MM-DD" :disabled="isViewMode" class="w-full" />
                                    </FormItem>
                                </a-col>
                            </a-row>
                        </div>
                    </div>
                </a-tab-pane>

                <!-- Tab 3: Adhésion et Bénéficiaires -->
                <a-tab-pane key="adhesion" tab="Informations d'Adhésion">
                    <div class="pt-4">
                        <div class="space-y-6">
                            <!-- Type d'adhésion -->
                            <FormItem label="Type d'adhésion" required :help="form.errors.type_adhesion">
                                <a-select v-model:value="form.type_adhesion" size="large" placeholder="Sélectionner le type" :disabled="isViewMode" @change="handleAdhesionTypeChange" class="w-full">
                                    <a-select-option value="individuelle">Individuelle</a-select-option>
                                    <a-select-option value="familiale">Familiale</a-select-option>
                                </a-select>
                            </FormItem>

                            <!-- Section Bénéficiaires -->
                            <div class="space-y-4">
                                <!-- Tableau des bénéficiaires -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-slate-700">
                                            <tr>
                                                <th class="py-1.5 px-3 text-left text-sm font-semibold text-white">Nom complet</th>
                                                <th class="py-1.5 px-3 text-left text-sm font-semibold text-white">Sexe</th>
                                                <th class="py-1.5 px-3 text-left text-sm font-semibold text-white">Date de naissance</th>
                                                <th class="py-1.5 px-3 text-left text-sm font-semibold text-white">Lien de parenté</th>
                                                <th class="py-1.5 px-3 text-center text-sm font-semibold text-white">
                                                    <a-button  v-if="form.type_adhesion === 'familiale'" type="primary" size="small" @click="addBeneficiaire" class="rounded-sm!" >
                                                        <template #icon><PlusOutlined /></template>
                                                    </a-button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 border ">
                                            <tr v-if="beneficiairesList.length === 0">
                                                <td colspan="5" class="text-center py-8 text-gray-400 border border-gray-200">
                                                    <a-empty/>

                                                </td>
                                            </tr>
                                            <tr v-for="(beneficiaire, index) in beneficiairesList" :key="index">
                                                <td class="p-0">
                                                    <a-input v-model:value="beneficiaire.nom_complet" size="large" placeholder="Nom complet" class="w-full! rounded-none!"/>
                                                </td>
                                                <td class="p-0">
                                                    <a-select v-model:value="beneficiaire.sexe" placeholder="Sexe" allow-clear size="large" class="w-full! rounded-none!">
                                                        <a-select-option value="M">Masculin</a-select-option>
                                                        <a-select-option value="F">Féminin</a-select-option>
                                                    </a-select>
                                                </td>
                                                <td class="p-0">
                                                    <a-date-picker v-model:value="beneficiaire.date_naissance" placeholder="Date" value-format="YYYY-MM-DD" allow-clear
                                                    size="large" class="w-full! rounded-none!"
                                                    />
                                                </td>
                                                <td class="p-0">
                                                    <a-select v-model:value="beneficiaire.lien_parente" placeholder="Lien de parenté" allow-clear size="large" class="rounded-none!">
                                                        <a-select-option value="conjoint">Conjoint(e)</a-select-option>
                                                        <a-select-option value="enfant">Enfant</a-select-option>
                                                    </a-select>
                                                </td>
                                                <td class="text-center p-0">
                                                    <a-button danger type="text" size="small" @click="removeBeneficiaire(index)">
                                                        <template #icon><DeleteOutlined /></template>
                                                    </a-button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </a-tab-pane>
            </a-tabs>
        </div>
    </BaseModal>
</template>

<script setup>
import BaseModal from '@/components/BaseModal/BaseModal.vue'
import FormInput from '@/components/Formulaire/FormInput.vue'
import FormItem from '@/components/Formulaire/FormItem.vue'
import { DeleteOutlined, PlusOutlined } from '@ant-design/icons-vue'
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
const activeTab = ref('identites')
const beneficiairesList = ref([])

const form = useForm({
    id: props.initialData.id || null,
    // Identités
    nom: props.initialData.nom || '',
    prenom: props.initialData.prenom || '',
    sexe: props.initialData.sexe || null,
    date_naissance: props.initialData.date_naissance || null,
    adresse: props.initialData.adresse || '',
    telephone: props.initialData.telephone || '',
    email: props.initialData.email || '',
    // Professionnels
    societe: props.initialData.societe || '',
    poste: props.initialData.poste || '',
    status: props.initialData.status || 'permanent',
    date_debut: props.initialData.date_debut || null,
    date_fin: props.initialData.date_fin || null,
    // Adhésion
    type_adhesion: props.initialData.type_adhesion || 'individuelle',
    // Bénéficiaires
    beneficiaires: props.initialData.beneficiaires || [],
})

// Gestion du changement de statut
const handleStatusChange = () => {
    if (form.status === 'permanent') {
        form.date_fin = null
    }
}

// Gestion du changement de type d'adhésion
const handleAdhesionTypeChange = () => {
    if (form.type_adhesion === 'individuelle') {
        beneficiairesList.value = []
        form.beneficiaires = []
    }
}

// Ajouter un bénéficiaire
const addBeneficiaire = () => {
    const newBeneficiaire = {
        nom_complet: '',
        sexe: null,
        date_naissance: null,
        lien_parente: null
    }
    beneficiairesList.value.push(newBeneficiaire)
    form.beneficiaires.push(newBeneficiaire)
}

// Supprimer un bénéficiaire
const removeBeneficiaire = (index) => {
    beneficiairesList.value.splice(index, 1)
    form.beneficiaires.splice(index, 1)
}

const titre = computed(() => {
    return props.initialData.id ? 'Modifier l\'ayant droit' : 'Ajouter un ayant droit'
})

// Nouveau AyantDroit
function addNew() {
    visible.value = true
    form.reset()
    form.clearErrors()
    form.id = null
    beneficiairesList.value = []
    form.beneficiaires = []
    activeTab.value = 'identites'
}

// Fonction pour modifier un ayant droit
const update = (rowData) => {
    router.visit(`${route('ayantdroit.show', {ayantdroit: rowData.id})}`, {
        preserveState: true,
        preserveScroll: true,
        only: ['flash'],
        onSuccess: (response) => {
            const responseData = response.props.flash.data
            Object.keys(responseData).forEach(key => {
                if (form.hasOwnProperty(key)) {
                    form[key] = responseData[key]
                }
            })

            // Charger les bénéficiaires si existants
            if (responseData.beneficiaires) {
                beneficiairesList.value = responseData.beneficiaires
                form.beneficiaires = responseData.beneficiaires
            }

            visible.value = true
            activeTab.value = 'identites'
        },
    })
}

async function handleSubmit() {
    loading.value = true
    form.clearErrors()

    try {
        const method = form.id ? 'put' : 'post'
        const url = form.id ? route('ayantdroit.update', form.id) : route('ayantdroit.store')

        // Les bénéficiaires sont déjà dans form.beneficiaires
        const submitData = form.data()

        router[method](url, submitData, {
            onSuccess: () => { handleClose() },
            onError: (errors) => { form.errors = errors},
        })

    } catch (error) {
        console.error('Erreur lors de la soumission:', error)
    }
    finally {
        loading.value = false
    }
}

function handleClose() {
    visible.value = false
    form.reset()
    form.clearErrors()
    beneficiairesList.value = []
    form.beneficiaires = []
    activeTab.value = 'identites'
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
:deep(.ant-tabs-nav) {
    margin-bottom: 0 !important;
}
:deep(.ant-tabs-tab) {
    white-space: normal;
    font-size: 14px;
    padding: 12px 20px !important;
}

</style>
