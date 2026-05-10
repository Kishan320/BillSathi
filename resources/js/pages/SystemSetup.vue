<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="text-muted-foreground hover:text-foreground transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </button>
      <div>
        <h1 class="text-xl font-semibold text-foreground">System Setup</h1>
        <p class="text-sm text-muted-foreground">Manage dropdown options used across the app</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Left tabs -->
      <div class="lg:col-span-1">
        <nav class="space-y-1">
          <button
            v-for="group in groups" :key="group.type"
            @click="activeType = group.type"
            :class="[
              'w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-left',
              activeType === group.type
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:text-foreground hover:bg-muted'
            ]"
          >
            <span class="text-base">{{ group.icon }}</span>
            {{ group.label }}
            <span class="ml-auto text-xs opacity-70">{{ (data[group.type] || []).length }}</span>
          </button>
        </nav>
      </div>

      <!-- Right content -->
      <div class="lg:col-span-3">
        <div class="bg-card border border-border rounded-xl">
          <!-- Section header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-border">
            <div>
              <h2 class="text-base font-semibold text-foreground">{{ activeGroup.label }}</h2>
              <p class="text-xs text-muted-foreground mt-0.5">{{ activeGroup.description }}</p>
            </div>
            <button @click="openModal()" class="flex items-center gap-1.5 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
              + Add
            </button>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="py-16 text-center text-sm text-muted-foreground">Loading...</div>

          <!-- Empty -->
          <div v-else-if="!activeList.length" class="py-16 text-center text-sm text-muted-foreground">
            No {{ activeGroup.label }} yet. Click "+ Add" to create one.
          </div>

          <!-- List -->
          <div v-else class="divide-y divide-border">
            <div
              v-for="(item, idx) in activeList" :key="item.id"
              class="flex items-center gap-3 px-6 py-3 hover:bg-muted/30 transition-colors"
            >
              <span class="text-xs text-muted-foreground w-6 text-right flex-shrink-0">{{ idx + 1 }}</span>
              <span class="flex-1 text-sm text-foreground">{{ item.value }}</span>
              <div class="flex items-center gap-1">
                <button @click="openModal(item)" title="Edit" class="p-1.5 rounded-lg bg-warning text-white hover:bg-warning transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                </button>
                <button @click="deleteItem(item)" title="Delete" class="p-1.5 rounded-lg bg-danger text-white hover:bg-danger/80 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add / Edit Modal -->
    <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-card rounded-2xl shadow-xl w-full max-w-sm mx-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h3 class="text-base font-semibold text-foreground">
            {{ modal.id ? 'Edit' : 'Add' }} {{ activeGroup.label }}
          </h3>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-5">
          <label class="block text-sm font-medium text-foreground mb-2">Name</label>
          <input
            v-model="modal.value"
            @keyup.enter="saveModal"
            :placeholder="`Enter ${activeGroup.label} name`"
            class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
            autofocus
          />
          <p v-if="modal.error" class="text-xs text-red-500 mt-1">{{ modal.error }}</p>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            CANCEL
          </button>
          <button @click="saveModal" :disabled="saving" class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? 'SAVING...' : (modal.id ? 'UPDATE' : 'CREATE') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';

const notify = useNotificationsStore();
const resourcesStore = useResourcesStore();

const groups = [
  { type: 'units',            label: 'Units',             description: 'Units of measurement used for items (Pcs, Kg, Ltr...)' },
  { type: 'tax_categories',   label: 'Tax Categories',    description: 'GST tax slabs applied to items' },
  { type: 'categories',       label: 'Item Categories',   description: 'Product/service categories for classification' },
  { type: 'stock_categories', label: 'Stock Categories',  description: 'Stock type classification (Raw Material, Finished Goods...)' },
];

const activeType  = ref('units');
const saving      = ref(false);
const modal       = reactive({ show: false, id: null, value: '', error: '' });

const emptySetupData = { units: [], tax_categories: [], categories: [], stock_categories: [] };
const data = computed(() => resourcesStore.systemSettings || emptySetupData);
const loading = computed(() => resourcesStore.systemSettingsLoading);
const activeGroup = computed(() => groups.find(g => g.type === activeType.value));
const activeList  = computed(() => data.value[activeType.value] || []);

const fetchData = async () => {
  await resourcesStore.fetchSystemSettings();
};

const openModal = (item = null) => {
  modal.id    = item?.id || null;
  modal.value = item?.value || '';
  modal.error = '';
  modal.show  = true;
};

const closeModal = () => { modal.show = false; };

const saveModal = async () => {
  if (!modal.value.trim()) { modal.error = 'Name is required'; return; }
  saving.value = true;
  try {
    if (modal.id) {
      await resourcesStore.updateSystemSetting(modal.id, { value: modal.value.trim() });
      notify.showSuccess('Updated', `"${modal.value.trim()}" updated successfully.`);
    } else {
      await resourcesStore.createSystemSetting({ type: activeType.value, value: modal.value.trim() });
      notify.showSuccess('Created', `"${modal.value.trim()}" added to ${activeGroup.value.label}.`);
    }
    closeModal();
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Something went wrong.');
  } finally {
    saving.value = false;
  }
};

const deleteItem = async (item) => {
  if (!confirm(`Delete "${item.value}"?`)) return;
  try {
    await resourcesStore.deleteSystemSetting(item);
    notify.showSuccess('Deleted', `"${item.value}" has been deleted.`);
  } catch {
    notify.showError('Delete Failed', 'Could not delete this item.');
  }
};

watch(activeType, () => { modal.show = false; });

onMounted(fetchData);
</script>
