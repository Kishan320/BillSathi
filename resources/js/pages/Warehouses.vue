<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">{{ $t('warehouses.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $t('warehouses.subtitle') }}</p>
      </div>
      <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        {{ $t('warehouses.new') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" :placeholder="$t('common.search')"
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th @click="setSort('name')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('warehouses.col_name') }} <span class="ml-1">{{ sortIcons['name'] }}</span>
            </th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('warehouses.col_city') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('warehouses.col_contact_person') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('warehouses.col_phone') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('warehouses.col_status') }}</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="9" class="text-center py-10">
              <div class="inline-flex items-center gap-2 text-muted-foreground">
                <AppIcon name="refresh-cw" :size="18" class="animate-spin text-primary" />
              </div>
            </td>
          </tr>
          <tr v-else-if="!rows.length"><td colspan="6" class="text-center py-10 text-muted-foreground">{{ $t('common.no_data', { resource: $t('warehouses.resource') }) }}</td></tr>
          <tr v-else v-for="wh in rows" :key="wh.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 font-medium text-foreground">{{ wh.name }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ wh.city || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ wh.contact_person || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ wh.phone || '—' }}</td>
            <td class="px-4 py-3">
              <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', wh.status == 1 ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger']">
                {{ wh.status == 1 ? $t('common.active') : $t('common.inactive') }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(wh)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deleteWarehouse(wh)" type="button" title="Delete"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-danger/10 text-danger transition-colors hover:bg-danger hover:text-white focus:outline-none focus:ring-2 focus:ring-danger/30">
                  <AppIcon name="trash-2" :size="15" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-4 py-3 border-t border-border">
        <p class="text-xs text-muted-foreground">
          {{ $t('common.showing', { from: meta.from, to: meta.to, total: meta.total, resource: $t('warehouses.resource') }) }}
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
            class="px-3 py-1.5 text-xs border border-border rounded-lg text-foreground hover:bg-muted disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            {{ $t('common.prev') }}
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" class="px-2 text-muted-foreground text-xs">…</span>
            <button v-else @click="goToPage(p)"
              :class="['px-3 py-1.5 text-xs border rounded-lg transition-colors', p === meta.current_page ? 'bg-primary text-primary-foreground border-primary' : 'border-border text-foreground hover:bg-muted']">
              {{ p }}
            </button>
          </template>
          <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1.5 text-xs border border-border rounded-lg text-foreground hover:bg-muted disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            {{ $t('common.next') }}
          </button>
        </div>
        <select v-model="perPage" @change="reload"
          class="px-2 py-1.5 text-xs bg-card border border-border rounded-lg text-foreground focus:outline-none">
          <option :value="10">{{ $t('common.per_page', { n: 10 }) }}</option>
          <option :value="25">{{ $t('common.per_page', { n: 25 }) }}</option>
          <option :value="50">{{ $t('common.per_page', { n: 50 }) }}</option>
        </select>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-card rounded-2xl shadow-card-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? $t('warehouses.edit_modal') : $t('warehouses.new_modal') }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="px-6 py-5 space-y-3">
          <!-- Name -->
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.name') }} <span class="text-danger">*</span></label>
            <input v-model="form.name" type="text" :placeholder="$t('warehouses.col_name')"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.name ? 'border-danger' : 'border-border']" />
            <p v-if="errors.name" class="text-xs text-danger mt-1">{{ errors.name }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.address') }}</label>
            <input v-model="form.address" type="text" :placeholder="$t('warehouses.address_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- City + Zip -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.city') }}</label>
              <input v-model="form.city" type="text" :placeholder="$t('common.city')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.zip_code') }}</label>
              <input v-model="form.zip_code" type="text" :placeholder="$t('common.zip_code')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>
          </div>

          <!-- Contact Person -->
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('warehouses.contact_person') }}</label>
            <input v-model="form.contact_person" type="text" :placeholder="$t('warehouses.contact_person_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- Phone + Email -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.phone') }}</label>
              <input v-model="form.phone" type="tel" :placeholder="$t('common.phone')"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.phone ? 'border-danger' : 'border-border']" />
              <p v-if="errors.phone" class="text-xs text-danger mt-1">{{ errors.phone }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.email') }}</label>
              <input v-model="form.email" type="email" :placeholder="$t('common.email')"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.email ? 'border-danger' : 'border-border']" />
              <p v-if="errors.email" class="text-xs text-danger mt-1">{{ errors.email }}</p>
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.status') }} <span class="text-danger">*</span></label>
            <select v-model="form.status"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.status ? 'border-danger' : 'border-border']">
              <option value="1">{{ $t('common.active') }}</option>
              <option value="0">{{ $t('common.inactive') }}</option>
            </select>
            <p v-if="errors.status" class="text-xs text-danger mt-1">{{ errors.status }}</p>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.notes') }}</label>
            <textarea v-model="form.notes" rows="2" :placeholder="$t('warehouses.notes_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" />
          </div>
        </div>

        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            {{ $t('common.cancel') }}
          </button>
          <button @click="saveWarehouse" :disabled="saving"
            class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? $t('common.saving') : (editId ? $t('common.update') : $t('common.create')) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, reactive } from 'vue';
import * as yup from 'yup';
import { useNotificationsStore } from '@/stores/notifications';
import { api } from '@/services/api';
import AppIcon from '@/components/ui/AppIcon.vue';

const notify = useNotificationsStore();

const saving = ref(false);
const showModal = ref(false);
const editId = ref(null);
const loading = ref(false);

const rows = ref([]);
const search = ref('');
const perPage = ref(10);
const sortCol = ref('name');
const sortDir = ref('asc');
const meta = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });

let searchTimer = null;

const defaultForm = () => ({
  name: '', address: '', city: '', zip_code: '',
  contact_person: '', phone: '', email: '', status: '1', notes: '',
});

const form = reactive(defaultForm());
const errors = reactive({});

const warehouseSchema = yup.object().shape({
  name: yup.string().required('Name is required.'),
  email: yup.string().email('Enter a valid email.').nullable().transform(v => v === '' ? null : v),
  phone: yup.string().nullable().transform(v => v === '' ? null : v),
  status: yup.string().required('Status is required.'),
});

const fetchDatatable = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get('/warehouses', {
      params: { page, per_page: perPage.value, search: search.value, sort_by: sortCol.value, sort_dir: sortDir.value },
    });
    const payload = data.data || data;
    rows.value = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : []);
    const total = data.total || data.meta?.total || rows.value.length;
    const lastPage = data.last_page || Math.max(1, Math.ceil(total / perPage.value));
    meta.value = { current_page: page, last_page: lastPage, from: total ? (page - 1) * perPage.value + 1 : 0, to: Math.min(page * perPage.value, total), total };
  } catch (e) {
    console.error(e);
    rows.value = [];
    meta.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
  } finally {
    loading.value = false;
  }
};

const reload = () => fetchDatatable(1);
const onSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(reload, 350); };
const setSort = (col) => {
  sortDir.value = sortCol.value === col ? (sortDir.value === 'asc' ? 'desc' : 'asc') : 'asc';
  sortCol.value = col;
  reload();
};
const sortIcons = computed(() => ({ name: sortCol.value !== 'name' ? '↕' : sortDir.value === 'asc' ? '↑' : '↓' }));
const goToPage = (page) => { if (page < 1 || page > meta.value.last_page) return; fetchDatatable(page); };
const pageNumbers = computed(() => {
  const { current_page: cur, last_page: last } = meta.value;
  const pages = [];
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= cur - 1 && i <= cur + 1)) pages.push(i);
    else if (pages[pages.length - 1] !== '...') pages.push('...');
  }
  return pages;
});

const validateForm = async () => {
  Object.keys(errors).forEach(k => delete errors[k]);
  try {
    await warehouseSchema.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner?.forEach(e => { if (e.path) errors[e.path] = e.message; });
    return false;
  }
};

const openModal = (wh = null) => {
  if (wh) {
    editId.value = wh.id;
    Object.assign(form, { ...defaultForm(), ...wh, status: String(wh.status) });
  } else {
    editId.value = null;
    Object.assign(form, defaultForm());
  }
  Object.keys(errors).forEach(k => delete errors[k]);
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const saveWarehouse = async () => {
  if (!await validateForm()) return;
  saving.value = true;
  try {
    const method = editId.value ? 'put' : 'post';
    const url = editId.value ? `/warehouses/${editId.value}` : '/warehouses';
    await api[method](url, form);
    notify.showSuccess(editId.value ? 'Warehouse Updated' : 'Warehouse Created', `"${form.name}" saved successfully.`);
    await reload();
    closeModal();
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Something went wrong.');
  } finally {
    saving.value = false;
  }
};

const deleteWarehouse = async (wh) => {
  if (!confirm(`Delete "${wh.name}"?`)) return;
  try {
    await api.delete(`/warehouses/${wh.id}`);
    notify.showSuccess('Warehouse Deleted', `"${wh.name}" has been deleted.`);
    await reload();
  } catch {
    notify.showError('Delete Failed', 'Could not delete the warehouse.');
  }
};

onMounted(() => reload());
</script>
