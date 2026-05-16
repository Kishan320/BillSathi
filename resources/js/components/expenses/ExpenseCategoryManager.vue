<template>
  <div v-if="open" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40">
    <div class="w-full max-w-lg bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-border">
        <h2 class="text-base font-semibold text-foreground">Manage Categories</h2>
        <button @click="emit('close')" class="text-muted-foreground hover:text-foreground transition-colors">✕</button>
      </div>

      <div class="p-5 space-y-4 max-h-[70vh] overflow-auto">
        <div class="flex gap-2">
          <input
            v-model="newValue"
            @keyup.enter="create"
            placeholder="New category name"
            class="flex-1 px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
          />
          <button
            @click="create"
            :disabled="saving || !newValue.trim()"
            class="px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
          >
            {{ saving ? 'ADDING...' : 'ADD' }}
          </button>
        </div>

        <div v-if="loading" class="py-10 text-center text-sm text-muted-foreground">Loading...</div>
        <div v-else-if="!rows.length" class="py-10 text-center text-sm text-muted-foreground">No categories yet.</div>

        <div v-else class="divide-y divide-border border border-border rounded-xl overflow-hidden">
          <div v-for="row in rows" :key="row.id" class="flex items-center gap-2 px-3 py-2">
            <input
              v-model="rowValues[row.id]"
              class="flex-1 px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
            />
            <button
              @click="save(row)"
              :disabled="saving || !rowValues[row.id]?.trim()"
              class="h-9 px-3 text-xs font-medium border border-border rounded-lg hover:bg-muted transition-colors disabled:opacity-50"
              title="Save"
            >
              SAVE
            </button>
            <button
              @click="remove(row)"
              :disabled="saving"
              class="h-9 px-3 text-xs font-medium bg-danger/10 text-danger rounded-lg hover:bg-danger hover:text-white transition-colors disabled:opacity-50"
              title="Delete"
            >
              DELETE
            </button>
          </div>
        </div>
      </div>

      <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2">
        <button @click="emit('close')" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';

const props = defineProps({
  open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'updated']);

const notify = useNotificationsStore();
const resourcesStore = useResourcesStore();

const loading = ref(false);
const saving = ref(false);
const newValue = ref('');

const rowValues = reactive({});

const rows = computed(() => resourcesStore.systemSettings?.categories || []);

const refresh = async () => {
  loading.value = true;
  try {
    await resourcesStore.fetchSystemSettings({ force: true });
    rows.value?.forEach?.(() => {});
    for (const r of rows.value) rowValues[r.id] = r.value;
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.open,
  (v) => {
    if (v) refresh();
  }
);

onMounted(() => {
  if (props.open) refresh();
});

const create = async () => {
  const value = newValue.value.trim();
  if (!value) return;
  saving.value = true;
  try {
    await resourcesStore.createSystemSetting({ type: 'categories', value });
    await resourcesStore.fetchSystemSettings({ force: true });
    newValue.value = '';
    for (const r of rows.value) rowValues[r.id] = r.value;
    notify.showSuccess('Created', `"${value}" added`);
    emit('updated');
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Could not add category');
  } finally {
    saving.value = false;
  }
};

const save = async (row) => {
  const value = (rowValues[row.id] || '').trim();
  if (!value) return;
  saving.value = true;
  try {
    await resourcesStore.updateSystemSetting(row.id, { value });
    await resourcesStore.fetchSystemSettings({ force: true });
    for (const r of rows.value) rowValues[r.id] = r.value;
    notify.showSuccess('Updated', `"${value}" saved`);
    emit('updated');
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Could not update category');
  } finally {
    saving.value = false;
  }
};

const remove = async (row) => {
  if (!confirm(`Delete "${row.value}"?`)) return;
  saving.value = true;
  try {
    await resourcesStore.deleteSystemSetting(row);
    await resourcesStore.fetchSystemSettings({ force: true });
    for (const r of rows.value) rowValues[r.id] = r.value;
    notify.showSuccess('Deleted', `"${row.value}" deleted`);
    emit('updated');
  } catch (e) {
    notify.showError('Delete Failed', e.response?.data?.message || 'Could not delete category');
  } finally {
    saving.value = false;
  }
};
</script>

