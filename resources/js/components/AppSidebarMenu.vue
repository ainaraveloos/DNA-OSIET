<template>
  <div class="bg-white px-2 lg:px-6 py-4 relative w-full">
    <!-- Navigation Container -->
    <div class="flex items-center justify-between">
      <!-- Bouton Précédent -->
      <button @click="previousMenu" :disabled="startIndex <= 0"
        class="hidden lg:flex items-center justify-center w-10 h-10 rounded-md border-1 border-[#CFCFCF] bg-white hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
        <i class="fas fa-arrow-left text-lg text-[#10B981]"></i>
      </button>

      <!-- Menus Container -->
      <div class="hidden lg:flex flex-col items-center">
        <!-- Main Menus Row -->
        <div class="flex flex-row">
          <div class="flex flex-row items-center justify-between space-x-4">
            <transition-group name="menu-slide" tag="div"
              class="flex flex-row items-center lg:space-x-1 2xl:space-x-4 space-x-4" appear>
              <div v-for="(menu, index) in visibleMenus" :key="menu.id" class="relative flex-shrink-0"
                @click="toggleSubmenu(menu.id)" @mouseleave="closeSubmenu"
                :style="{ transitionDelay: `${index * 100}ms` }">

                <!-- Menu Item -->
                <div
                  class="bg-white shadow-md border-r-2 cursor-pointer hover:shadow-xl transition-shadow duration-200 relative transform hover:scale-105"
                  style="border-color: rgba(0, 119, 255, 0.5);">
                  <div class="flex items-center p-2">
                    <!-- Icon -->
                    <div
                      class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl mr-3 flex-shrink-0 transform transition-transform duration-200"
                      :style="{ backgroundColor: menu.color }">
                      <i :class="menu.icon" class="text-lg"></i>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 w-full items-center">
                      <h3 class="text-[#1F2937] font-semibold text-md text-center truncate">{{ menu.title }}</h3>
                      <p class="text-gray-500 text-xs text-center truncate">{{ menu.description }}</p>
                    </div>

                    <!-- Dropdown Arrow -->
                    <i class="fas fa-chevron-down text-gray-400 ml-2 flex-shrink-0 transform transition-transform duration-200"
                      :class="{ 'rotate-180': activeSubmenu === menu.id }"></i>
                  </div>
                </div>

                <!-- Submenu -->
                <transition name="submenu-slide">
                  <div v-if="activeSubmenu === menu.id"
                    class="absolute bg-white shadow-xl border-r-2 border-blue-300 z-50 w-full">
                    <div class="p-0">
                      <transition-group name="submenu-item" tag="div">
                        <div v-for="(item, itemIndex) in menu.submenuItems" :key="itemIndex"
                          class="flex items-center p-2 lg:gap-2 cursor-pointer transition-all duration-200 border-b border-gray-100 last:border-b-0 transform hover:translate-x-1"
                          :style="{
                            transitionDelay: `${itemIndex * 50}ms`,
                            '--hover-color': menu.hoverColor

                          }" :class="['text-gray-700', item.active ? 'font-semibold' : '', 'submenu-item']"
                          @click="item.action">
                          <i :class="item.icon" class="w-4 h-4 flex-shrink-0"></i>
                          <span class="text-sm font-medium">{{ item.label }}</span>
                          <i v-if="item.hasSubmenu"
                            class="fas fa-chevron-right ml-auto text-xs transform transition-transform duration-200 hover:translate-x-1"></i>
                        </div>
                      </transition-group>
                    </div>
                  </div>
                </transition>
              </div>
            </transition-group>
          </div>
        </div>
      </div>

      <!-- Bouton Burger (visible uniquement sur mobile) -->
      <div class="lg:hidden flex items-center justify-between w-full">
        <!-- Bouton burger -->
        <button @click="showMobileMenu = true"
          class="flex items-center justify-center w-10 h-10 rounded-md border border-gray-300 bg-white hover:bg-gray-100">
          <i class="fas fa-bars text-gray-600 text-xl"></i>
        </button>
        <!-- Texte à côté du burger -->
        <div class="flex items-center gap-2 text-gray-600 text-sm font-medium">
          <i class="fas fa-arrow-left"></i>
          <span class="text-md">Tous les menus ici</span>
        </div>
      </div>

      <!-- Drawer Mobile -->
      <transition name="submenu-slide">
        <div v-if="showMobileMenu" class="fixed top-4 left-0 h-full w-64 bg-white shadow-xl z-50 overflow-y-auto"
          @mouseleave="showMobileMenu = false; closeSubmenu()">

          <!-- Header -->
          <div class="flex justify-between items-center p-3 border-b border-gray-200">
            <h2 class="font-bold text-gray-700">Menu</h2>
            <button @click="showMobileMenu = false">
              <i class="fas fa-times text-gray-600 text-lg"></i>
            </button>
          </div>

          <!-- Tous les menus -->
          <div v-for="menu in allMenus" :key="menu.id" class="border-b border-gray-100" @mouseleave="closeSubmenu">

            <!-- Menu principal -->
            <div class="flex items-center p-2 cursor-pointer hover:bg-gray-50" @click="toggleSubmenu(menu.id)">
              <!-- Icône -->
              <div class="w-8 h-8 flex items-center justify-center rounded-md text-white mr-2"
                :style="{ backgroundColor: menu.color }">
                <i :class="menu.icon" class="text-sm"></i>
              </div>
              <!-- Titre -->
              <span class="text-sm font-medium text-gray-700">{{ menu.title }}</span>
              <i class="fas fa-chevron-down ml-auto text-xs text-gray-400"
                :class="{ 'rotate-180': activeSubmenu === menu.id }"></i>
            </div>

            <!-- Sous-menus -->
            <transition name="submenu-slide">
              <div v-if="activeSubmenu === menu.id" class="ml-10" @mouseleave="closeSubmenu">

                <div v-for="(item, idx) in menu.submenuItems" :key="idx"
                  class="flex items-center p-2 gap-2 text-xs text-gray-600 hover:text-blue-600 cursor-pointer">
                  <i :class="item.icon" class="w-4"></i>
                  {{ item.label }}
                </div>

              </div>
            </transition>
          </div>
        </div>
      </transition>



      <!-- Bouton Suivant -->
      <button @click="nextMenu" :disabled="startIndex + maxVisibleMenus >= allMenus.length"
        class="hidden lg:flex items-center justify-center w-10 h-10 rounded-md border-1 border-[#CFCFCF] bg-white hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
        <i class="fas fa-arrow-right text-lg text-[#10B981]"></i>
      </button>
    </div>

    <!-- Expand/Collapse Arrow -->
    <transition name="expand-arrow" appear>
      <div v-if="hasHiddenMenus" class="absolute left-1/2 transform -translate-x-1/2 -mt-3" style="top: 100%;">
        <button @click="toggleExpandAll"
          class="hidden lg:flex items-center justify-center w-6 h-6 rounded-full bg-gradient-to-r from-teal-400 to-teal-600 hover:from-teal-500 hover:to-teal-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110"
          :class="{ 'rotate-180': showAllMenus }">
          <i class="fas fa-chevron-down text-sm transform transition-transform duration-300"></i>
        </button>
      </div>
    </transition>

    <!-- Expanded Menus -->
    <transition name="expanded-menus">
      <div v-if="showAllMenus && hiddenMenus.length > 0" class="mt-4 w-full flex flex-wrap justify-center gap-4">
        <transition-group name="expanded-menu-slide" tag="div" class="flex flex-wrap justify-center gap-4" appear>
          <div v-for="(menu, index) in hiddenMenus" :key="`expanded-${menu.id}`"
            class="relative flex-shrink-0 cursor-pointer mx-auto" @click="toggleSubmenu(menu.id)"
            @mouseleave="closeSubmenu">

            <!-- Menu Item -->
            <div
              class="bg-white shadow-md border-r-2 border-blue-300 hover:shadow-xl transition-shadow duration-200 transform hover:scale-105 p-3">
              <div class="flex items-center justify-center">
                <!-- Icon -->
                <div class="w-12 h-10 rounded-lg flex items-center justify-center text-white text-xl mr-3 flex-shrink-0"
                  :style="{ backgroundColor: menu.color }">
                  <i :class="menu.icon" class="text-lg"></i>
                </div>
                <!-- Content -->
                <div class="flex-1 items-center text-center">
                  <h3 class="text-[#1F2937] font-semibold text-md truncate">{{ menu.title }}</h3>
                  <p class="text-gray-500 text-xs truncate">{{ menu.description }}</p>
                </div>
                <!-- Dropdown Arrow -->
                <i class="fas fa-chevron-down text-gray-400 ml-2 flex-shrink-0"
                  :class="{ 'rotate-180': activeSubmenu === menu.id }"></i>
              </div>
            </div>

            <!-- Sous-menu -->
            <transition name="submenu-slide">
              <div v-if="activeSubmenu === menu.id"
                class="absolute bg-white shadow-xl border-r-2 border-blue-400 z-50 w-full">
                <div class="p-0">
                  <transition-group name="submenu-item" tag="div">
                    <div v-for="(item, itemIndex) in menu.submenuItems" :key="itemIndex"
                      class="flex items-center p-3 cursor-pointer transition-all duration-200 border-b border-gray-100 last:border-b-0 transform hover:translate-x-1"
                      :style="{
                        transitionDelay: `${itemIndex * 50}ms`,
                        '--hover-color': menu.hoverColor
                      }" :class="['text-gray-700', item.active ? 'font-semibold' : '', 'submenu-item']">
                      <i :class="item.icon" class="w-4 h-5 flex-shrink-0"></i>
                      <span class="text-sm font-medium">{{ item.label }}</span>
                      <i v-if="item.hasSubmenu"
                        class="fas fa-chevron-right ml-auto text-xs transform transition-transform duration-200 hover:translate-x-1"></i>
                    </div>
                  </transition-group>
                </div>
              </div>
            </transition>
          </div>
        </transition-group>
      </div>
    </transition>
  </div>

  <!-- Backdrop -->
  <transition name="backdrop">
    <div v-if="activeSubmenu" class="fixed" @click="closeSubmenu"></div>
  </transition>

</template>

<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface SubmenuItem {
  label: string
  icon: string
  active?: boolean
  hasSubmenu?: boolean
}

interface MenuItem {
  id: string
  title: string
  description: string
  color: string
  icon: string
  hoverColor?: string
  submenuItems: SubmenuItem[]
}

const listeSociete = () => {
  router.get(route("societe.index"));
}

const suiviCotisation = () => {
  router.get(route("societe.suivi"));
}

const historique = () => {
  router.get(route("societe.histori"));
}

const Paiement = () => {
  router.get(route("societe.Paiement"));
}

const alt = () => {
  router.get(route("societe.alt"));
}

const LAD = () => {
  router.get(route("ayantdroit.index"));
}

const Badges = () => {
  router.get(route("AyantsEndroit.Badges"));
}

const Qrcode = () => {
  router.get(route("AyantsEndroit.Qrcode"));
}

const SuiviA = () => {
  router.get(route("AyantsEndroit.SuiviA"));
}

const Infos = () => {
  router.get(route("AyantsEndroit.Infos"));
}

const Benefices = () => {
  router.get(route("AyantsEndroit.Benefices"));
}

const Historie = () => {
  router.get(route("AyantsEndroit.Historie"));
}

// === Données des menus ===
const allMenus: MenuItem[] = [
  {
    id: 'societies',
    title: 'Gestion des Sociétés',
    description: 'Gérer les entreprises et leurs cotisations',
    color: '#10B981',
    icon: 'fas fa-building',
    hoverColor: '#10B981',
    submenuItems: [
      { label: 'Liste des Sociétés', icon: 'fas fa-list', action: listeSociete },
      { label: 'Suivi Cotisations', icon: 'fas fa-money-bill-wave', action: suiviCotisation },
      { label: 'Historique', icon: 'fas fa-history', action: historique },
      { label: 'Paiements', icon: 'fas fa-credit-card', action: Paiement },
      { label: 'Alertes', icon: 'fas fa-exclamation-triangle', action: alt },
    ]
  },
  {
    id: 'ayants-droits',
    title: 'Gestion des Ayants Droits',
    description: 'Gérer les bénéficiaires et leurs badges',
    color: '#8B5CF6',
    hoverColor: '#754EA7',
    icon: 'fas fa-users',
    submenuItems: [
      { label: 'Liste des Ayants Droits', icon: 'fas fa-list', action: LAD },
      { label: 'Gestion des Badges', icon: 'fas fa-id-badge', action: Badges },
      { label: 'Gestion QR Code', icon: 'fas fa-qrcode', action: Qrcode },
      { label: 'Suivi Adhésions', icon: 'fas fa-clipboard-check', action: SuiviA },
      { label: 'Infos Personnelles', icon: 'fas fa-info-circle', action: Infos },
      { label: 'Bénéficiaires', icon: 'fas fa-user-friends', action: Benefices },
      { label: 'Historique', icon: 'fas fa-history', action: Historie },
    ]
  },
  {
    id: 'patients',
    title: 'Gestion des Patients',
    description: 'Gestion complète des dossiers patients',
    color: '#3B82F6',
    icon: 'fas fa-user-injured',
    hoverColor: '#2563EB',
    submenuItems: [
      { label: 'Nouveaux Patients', icon: 'fas fa-user-plus' },
      { label: 'Dossiers Médicaux', icon: 'fas fa-file-medical' },
      { label: 'Rendez-vous', icon: 'fas fa-calendar-alt' },
      { label: 'Historique', icon: 'fas fa-history' }
    ]
  },
  {
    id: 'pharmacy',
    title: 'Gestion Pharmacie',
    description: 'Gestion complète du stock pharmaceutique',
    color: '#EC4899',
    icon: 'fas fa-pills',
    hoverColor: '#EC4899',
    submenuItems: [
      { label: 'Stock Médicaments', icon: 'fas fa-pills' },
      { label: 'Commandes', icon: 'fas fa-shopping-cart' },
      { label: 'Fournisseurs', icon: 'fas fa-truck' },
      { label: 'Rapports', icon: 'fas fa-chart-bar' }
    ]
  },
  {
    id: 'medecins',
    title: 'Gestion des Médecins',
    description: 'Base complète des médecins',
    color: '#06B6D4',
    icon: 'fas fa-stethoscope',
    hoverColor: '#06B6D4',
    submenuItems: [
      { label: 'Liste Médecins', icon: 'fas fa-user-md' },
      { label: 'Spécialités', icon: 'fas fa-stethoscope' },
      { label: 'Planning', icon: 'fas fa-calendar' }
    ]
  },
  {
    id: 'urgences',
    title: 'Gestion des Urgences',
    description: 'Gestion urgente et prioritaire',
    color: '#F3001A',
    icon: 'fas fa-ambulance',
    hoverColor: '#F3001A',
    submenuItems: [
      { label: 'Urgences Actives', icon: 'fas fa-exclamation-circle' },
      { label: 'Équipes Urgence', icon: 'fas fa-users' },
      { label: 'Matériel Urgence', icon: 'fas fa-first-aid' }
    ]
  },
  {
    id: 'statistiques',
    title: 'Statistiques et Rapports',
    description: 'Analyses et données complètes',
    color: '#8B5CF6',
    icon: 'fas fa-chart-line',
    hoverColor: '#8B5CF6',
    submenuItems: [
      { label: 'Tableaux de Bord', icon: 'fas fa-tachometer-alt' },
      { label: 'Rapports Mensuels', icon: 'fas fa-file-alt' },
      { label: 'Analyses', icon: 'fas fa-chart-bar' }
    ]
  },
  {
    id: 'parametres',
    title: 'Paramètres et Sécurité',
    description: 'Configuration et sécurité système',
    color: '#000000',
    icon: 'fas fa-cog',
    hoverColor: '#000000',
    submenuItems: [
      { label: 'Utilisateurs', icon: 'fas fa-users-cog' },
      { label: 'Permissions', icon: 'fas fa-shield-alt' },
      { label: 'Sauvegardes', icon: 'fas fa-database' },
      { label: 'Configuration', icon: 'fas fa-cogs' }
    ]
  }
]

// === Réactifs ===
const startIndex = ref(0)
const activeSubmenu = ref<string | null>(null)
const showAllMenus = ref(false)
const showMobileMenu = ref(false)
const maxVisibleMenus = ref(5)

// Fonction pour mettre à jour maxVisibleMenus selon écran
const updateResponsive = () => {
  const width = window.innerWidth
  if (width >= 1536) { // 2XL
    maxVisibleMenus.value = 4
  } else if (width >= 1280) { // XL
    maxVisibleMenus.value = 3
  } else if (width >= 1024) { // LG
    maxVisibleMenus.value = 3
  } else if (width >= 768) { // MD
    maxVisibleMenus.value = 3
  } else { // SM
    maxVisibleMenus.value = 2
  }
}

onMounted(() => {
  updateResponsive()
  window.addEventListener('resize', updateResponsive)
})
onUnmounted(() => window.removeEventListener('resize', updateResponsive))

// Computed
const visibleMenus = computed(() => {
  if (showAllMenus.value) return allMenus.slice(0, maxVisibleMenus.value)
  return allMenus.slice(startIndex.value, startIndex.value + maxVisibleMenus.value)
})

const hiddenMenus = computed(() => allMenus.slice(maxVisibleMenus.value))

const hasHiddenMenus = computed(() => allMenus.length > maxVisibleMenus.value)

// Méthodes
const previousMenu = () => {
  if (startIndex.value > 0) {
    startIndex.value--
    activeSubmenu.value = null
  }
}

const nextMenu = () => {
  if (startIndex.value + maxVisibleMenus.value < allMenus.length) {
    startIndex.value++
    activeSubmenu.value = null
  }
}

const toggleExpandAll = () => {
  showAllMenus.value = !showAllMenus.value
  activeSubmenu.value = null
  if (!showAllMenus.value) startIndex.value = 0
}

const toggleSubmenu = (menuId: string) => {
  activeSubmenu.value = activeSubmenu.value === menuId ? null : menuId
}

const closeSubmenu = () => (activeSubmenu.value = null)
</script>

<style scoped>
/* Import Font Awesome */
@import '@fortawesome/fontawesome-free/css/all.css';

/* Custom shadow for menu items */
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Transition effects */
.transition-shadow {
  transition: box-shadow 0.2s ease-in-out;
}

.transition-colors {
  transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

/* Vue Transitions */

/* Menu slide animations */
.menu-slide-enter-active,
.menu-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.menu-slide-enter-from {
  opacity: 0;
  transform: translateX(30px) scale(0.9);
}

.menu-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px) scale(0.9);
}

/* Submenu animations */
.submenu-slide-enter-active {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.submenu-slide-leave-active {
  transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.submenu-slide-enter-from {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.submenu-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.98);
}

/* Submenu item animations */
.submenu-item-enter-active {
  transition: all 0.2s ease-out;
}

.submenu-item-enter-from {
  opacity: 0;
  transform: translateX(-15px);
}

/* Expand arrow animations */
.expand-arrow-enter-active {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.expand-arrow-enter-from {
  opacity: 0;
  transform: translateY(10px) scale(0.8);
}

/* Expanded menus animations */
.expanded-menus-enter-active {
  transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.expanded-menus-leave-active {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.expanded-menus-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.expanded-menus-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Expanded menu slide animations */
.expanded-menu-slide-enter-active {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.expanded-menu-slide-enter-from {
  opacity: 0;
  transform: translateY(15px) scale(0.9);
}

/* Backdrop animation */
.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 1280px) {
  .space-x-10> :not([hidden])~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1.5rem * var(--tw-space-x-reverse));
    margin-left: calc(1.5rem * calc(1 - var(--tw-space-x-reverse)));
  }
}

@media (max-width: 1024px) {
  .space-x-10> :not([hidden])~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(1rem * var(--tw-space-x-reverse));
    margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
  }
}

@media (max-width: 768px) {
  .space-x-10> :not([hidden])~ :not([hidden]) {
    --tw-space-x-reverse: 0;
    margin-right: calc(0.5rem * var(--tw-space-x-reverse));
    margin-left: calc(0.5rem * calc(1 - var(--tw-space-x-reverse)));
  }

  .px-6 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

@media (max-width: 640px) {
  .flex-row {
    flex-direction: column;
    gap: 1rem;
  }

  .space-x-10 {
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .space-x-10> :not([hidden])~ :not([hidden]) {
    margin: 0;
  }
}

.submenu-item:hover {
  color: var(--hover-color) !important;
  background-color: rgba(0, 0, 0, 0.03);
}
</style>
