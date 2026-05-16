<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">{{ $t('vendors.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $t('vendors.subtitle') }}</p>
      </div>
      <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        {{ $t('vendors.new') }}
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
            <th @click="setSort('billing_name')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('vendors.col_name') }} <span class="ml-1">{{ sortIcons['billing_name'] }}</span>
            </th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('vendors.col_mobile') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('vendors.col_tax_number') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('vendors.col_billing_address') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('vendors.col_city') }}</th>
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
          <tr v-else-if="!rows.length"><td colspan="6" class="text-center py-10 text-muted-foreground">{{ $t('common.no_data', { resource: $t('vendors.resource') }) }}</td></tr>
          <tr v-else v-for="vendor in rows" :key="vendor.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 font-medium text-foreground">{{ vendor.billing_name }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ vendor.mobile_number || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ vendor.tax_number || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground text-xs">{{ vendor.billing_address || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ vendor.city || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(vendor)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deleteVendor(vendor)" type="button" title="Delete"
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
          {{ $t('common.showing', { from: meta.from, to: meta.to, total: meta.total, resource: $t('vendors.resource') }) }}
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
          <option :value="100">{{ $t('common.per_page', { n: 100 }) }}</option>
        </select>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-card rounded-2xl shadow-card-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? $t('vendors.edit_modal') : $t('vendors.new_modal') }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="px-6 py-5 space-y-5">
          <!-- Billing Information Section -->
          <div>
            <h3 class="text-sm font-semibold text-foreground mb-4">{{ $t('vendors.billing_info') }}</h3>
            
            <!-- Billing Name + Mobile -->
            <div class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.billing_name') }} <span class="text-danger">*</span></label>
                <input v-model="form.billing_name" type="text" :placeholder="$t('vendors.billing_name')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.billing_name ? 'border-danger' : 'border-border']" />
                <p v-if="errors.billing_name" class="text-xs text-danger mt-1">{{ errors.billing_name }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.mobile_number') }} <span class="text-danger">*</span></label>
                <input v-model="form.mobile_number" type="tel" :placeholder="$t('vendors.mobile_number')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.mobile_number ? 'border-danger' : 'border-border']" />
                <p v-if="errors.mobile_number" class="text-xs text-danger mt-1">{{ errors.mobile_number }}</p>
              </div>
            </div>

            <!-- Tax Number + Payment Terms -->
            <div class="grid grid-cols-2 gap-3 mb-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.tax_number') }}</label>
                <input v-model="form.tax_number" type="text" :placeholder="$t('vendors.tax_number')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.payment_terms') }}</label>
                <input v-model="form.payment_terms" type="text" :placeholder="$t('vendors.payment_terms_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
            </div>

            <!-- Billing Address -->
            <div class="mb-3">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.billing_address') }} <span class="text-danger">*</span></label>
              <input v-model="form.billing_address" type="text" :placeholder="$t('vendors.address_placeholder')"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.billing_address ? 'border-danger' : 'border-border']" />
              <p v-if="errors.billing_address" class="text-xs text-danger mt-1">{{ errors.billing_address }}</p>
            </div>

            <!-- Address Line 2 -->
            <div class="mb-3">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.address_line2') }}</label>
              <input v-model="form.billing_address_line2" type="text" :placeholder="$t('vendors.address_line2_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>

            <!-- City + State + Country -->
            <div class="grid grid-cols-3 gap-3 mb-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.city') }} <span class="text-danger">*</span></label>
                <input v-model="form.city" type="text" :placeholder="$t('common.city')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.city ? 'border-danger' : 'border-border']" />
                <p v-if="errors.city" class="text-xs text-danger mt-1">{{ errors.city }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.state') }}</label>
                <input v-model="form.state" type="text" :placeholder="$t('common.state')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.country') }}</label>
                <input v-model="form.country" type="text" :placeholder="$t('common.country')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
            </div>

            <!-- Zip Code -->
            <div class="mb-3">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.zip_code') }}</label>
              <input v-model="form.zip_code" type="text" :placeholder="$t('common.zip_code')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>

            <!-- Notes -->
            <div class="mb-3">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.notes') }}</label>
              <textarea v-model="form.notes" :placeholder="$t('vendors.notes_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" rows="2" />
            </div>
          </div>

          <!-- Shipping Address Section -->
          <div>
            <div class="flex items-center gap-2 mb-4">
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.same_as_billing" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                {{ $t('vendors.shipping_same') }}
              </label>
            </div>

            <div v-if="!form.same_as_billing" class="space-y-3 border-t border-border pt-4">
              <h3 class="text-sm font-semibold text-foreground">{{ $t('vendors.shipping_info') }}</h3>

              <!-- Shipping Name -->
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.shipping_name') }} <span class="text-danger">*</span></label>
                <input v-model="form.shipping_name" type="text" :placeholder="$t('vendors.shipping_name')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_name ? 'border-danger' : 'border-border']" />
                <p v-if="!form.same_as_billing && errors.shipping_name" class="text-xs text-danger mt-1">{{ errors.shipping_name }}</p>
              </div>

              <!-- Shipping Address -->
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.shipping_address') }} <span class="text-danger">*</span></label>
                <input v-model="form.shipping_address" type="text" :placeholder="$t('vendors.address_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_address ? 'border-danger' : 'border-border']" />
                <p v-if="!form.same_as_billing && errors.shipping_address" class="text-xs text-danger mt-1">{{ errors.shipping_address }}</p>
              </div>

              <!-- Shipping Address Line 2 -->
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('vendors.address_line2') }}</label>
                <input v-model="form.shipping_address_line2" type="text" :placeholder="$t('vendors.address_line2_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>

              <!-- Shipping City + State + Country -->
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.city') }} <span class="text-danger">*</span></label>
                  <input v-model="form.shipping_city" type="text" :placeholder="$t('common.city')"
                    :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_city ? 'border-danger' : 'border-border']" />
                  <p v-if="!form.same_as_billing && errors.shipping_city" class="text-xs text-danger mt-1">{{ errors.shipping_city }}</p>
                </div>
                <div>
                  <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.state') }} <span class="text-danger">*</span></label>
                  <input v-model="form.shipping_state" type="text" :placeholder="$t('common.state')"
                    :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_state ? 'border-danger' : 'border-border']" />
                  <p v-if="!form.same_as_billing && errors.shipping_state" class="text-xs text-danger mt-1">{{ errors.shipping_state }}</p>
                </div>
                <div>
                  <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.country') }} <span class="text-danger">*</span></label>
                  <input v-model="form.shipping_country" type="text" :placeholder="$t('common.country')"
                    :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_country ? 'border-danger' : 'border-border']" />
                  <p v-if="!form.same_as_billing && errors.shipping_country" class="text-xs text-danger mt-1">{{ errors.shipping_country }}</p>
                </div>
              </div>

              <!-- Shipping Zip Code -->
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.zip_code') }} <span class="text-danger">*</span></label>
                <input v-model="form.shipping_zip_code" type="text" :placeholder="$t('common.zip_code')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', !form.same_as_billing && errors.shipping_zip_code ? 'border-danger' : 'border-border']" />
                <p v-if="!form.same_as_billing && errors.shipping_zip_code" class="text-xs text-danger mt-1">{{ errors.shipping_zip_code }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            {{ $t('common.cancel') }}
          </button>
          <button @click="saveVendor" :disabled="saving"
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
import { useVendorsStore } from '@/stores/vendors';
import AppIcon from '@/components/ui/AppIcon.vue';

const notify = useNotificationsStore();
const vendorsStore = useVendorsStore();

const saving = ref(false);
const showModal = ref(false);
const editId = ref(null);

// DataTable state — driven by store
const rows = computed(() => vendorsStore.list);
const meta = computed(() => vendorsStore.meta);
const loading = computed(() => vendorsStore.loading);
const search = ref('');
const perPage = ref(10);
const sortCol = ref('billing_name');
const sortDir = ref('asc');

let searchTimer = null;

const COLUMNS = ['billing_name', 'mobile_number', 'tax_number', 'billing_address', 'city'];

const fetchDatatable = (page = 1) =>
  vendorsStore.fetchList({
    page,
    per_page: perPage.value,
    search: search.value,
    sort_by: sortCol.value,
    sort_dir: sortDir.value,
  });

const reload = () => fetchDatatable(1);

const onSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(reload, 350);
};

const setSort = (col) => {
  if (sortCol.value === col) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  else { sortCol.value = col; sortDir.value = 'asc'; }
  reload();
};

const sortIcons = computed(() => {
  const icons = {};
  COLUMNS.forEach((col) => {
    icons[col] = sortCol.value !== col ? '↕' : sortDir.value === 'asc' ? '↑' : '↓';
  });
  return icons;
});

const goToPage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchDatatable(page);
};

const pageNumbers = computed(() => {
  const { current_page: cur, last_page: last } = meta.value;
  const pages = [];
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= cur - 1 && i <= cur + 1)) pages.push(i);
    else if (pages[pages.length - 1] !== '...') pages.push('...');
  }
  return pages;
});

// ── Form ──────────────────────────────────────────────────────────────────
const defaultForm = () => ({
  billing_name: '',
  mobile_number: '',
  tax_number: '',
  payment_terms: '',
  billing_address: '',
  billing_address_line2: '',
  city: '',
  state: '',
  country: '',
  zip_code: '',
  notes: '',
  same_as_billing: 1,
  shipping_name: '',
  shipping_address: '',
  shipping_address_line2: '',
  shipping_city: '',
  shipping_state: '',
  shipping_country: '',
  shipping_zip_code: '',
});

const form = reactive(defaultForm());
const errors = reactive({});

const vendorSchema = yup.object().shape({
  billing_name: yup.string().required('Billing name is required.'),
  mobile_number: yup.string().required('Mobile number is required.'),
  billing_address: yup.string().required('Billing address is required.'),
  city: yup.string().required('City is required.'),
  shipping_name: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping name is required.'),
    otherwise: (s) => s.notRequired(),
  }),
  shipping_address: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping address is required.'),
    otherwise: (s) => s.notRequired(),
  }),
  shipping_city: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping city is required.'),
    otherwise: (s) => s.notRequired(),
  }),
  shipping_state: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping state is required.'),
    otherwise: (s) => s.notRequired(),
  }),
  shipping_country: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping country is required.'),
    otherwise: (s) => s.notRequired(),
  }),
  shipping_zip_code: yup.string().when('same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping zip code is required.'),
    otherwise: (s) => s.notRequired(),
  }),
});

const validateForm = async () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await vendorSchema.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner?.forEach((e) => { if (e.path) errors[e.path] = e.message; });
    return false;
  }
};

const openModal = (vendor = null) => {
  editId.value = vendor?.id ?? null;
  Object.assign(form, defaultForm(), vendor ?? {});
  Object.keys(errors).forEach((k) => delete errors[k]);
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const saveVendor = async () => {
  if (!await validateForm()) return;
  saving.value = true;
  try {
    if (editId.value) {
      await vendorsStore.updateVendor(editId.value, form);
      notify.showSuccess('Vendor Updated', `"${form.billing_name}" updated successfully.`);
    } else {
      await vendorsStore.createVendor(form);
      notify.showSuccess('Vendor Created', `"${form.billing_name}" created successfully.`);
    }
    closeModal();
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Something went wrong.');
  } finally {
    saving.value = false;
  }
};

const deleteVendor = async (vendor) => {
  if (!confirm(`Delete "${vendor.billing_name}"?`)) return;
  try {
    await vendorsStore.deleteVendor(vendor.id);
    notify.showSuccess('Vendor Deleted', `"${vendor.billing_name}" has been deleted.`);
  } catch {
    notify.showError('Delete Failed', 'Could not delete the vendor.');
  }
};

onMounted(reload);
</script>
