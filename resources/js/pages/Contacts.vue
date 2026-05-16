<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">{{ $t('contacts.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $t('contacts.subtitle') }}</p>
      </div>
      <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        {{ $t('contacts.new') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" :placeholder="$t('common.search')"
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterType" @change="reload"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">{{ $t('contacts.filter_all') }}</option>
        <option value="customer">{{ $t('contacts.type_customer') }}</option>
        <option value="vendor">{{ $t('contacts.type_vendor') }}</option>
        <option value="both">{{ $t('contacts.type_both') }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th @click="setSort('name')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('contacts.col_name') }} <span class="ml-1">{{ sortIcons['name'] }}</span>
            </th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('contacts.col_type') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('contacts.col_mobile') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('contacts.col_email') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('contacts.col_gstin') }}</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('contacts.col_city') }}</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="text-center py-10">
              <div class="inline-flex items-center gap-2 text-muted-foreground">
                <AppIcon name="refresh-cw" :size="18" class="animate-spin text-primary" />
              </div>
            </td>
          </tr>
          <tr v-else-if="!rows.length"><td colspan="7" class="text-center py-10 text-muted-foreground">{{ $t('common.no_data', { resource: $t('contacts.resource') }) }}</td></tr>
          <tr v-else v-for="contact in rows" :key="contact.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 font-medium text-foreground">{{ contact.name }}</td>
            <td class="px-4 py-3">
              <span :class="typeClass(contact.type)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ typeLabel(contact.type) }}
              </span>
            </td>
            <td class="px-4 py-3 text-muted-foreground">{{ contact.mobile || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground text-xs">{{ contact.email || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ contact.gstin || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ contact.billing_city || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(contact)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deleteContact(contact)" type="button" title="Delete"
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
          {{ $t('common.showing', { from: meta.from, to: meta.to, total: meta.total, resource: $t('contacts.resource') }) }}
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
          <h2 class="text-base font-semibold text-foreground">{{ editId ? $t('contacts.edit_modal') : $t('contacts.new_modal') }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-border px-6">
          <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
            :class="['px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors', activeTab === t.key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']">
            {{ t.label }}
          </button>
        </div>

        <div class="px-6 py-5 space-y-5">
          <!-- Identity Tab -->
          <div v-show="activeTab === 'identity'" class="space-y-4">
            <!-- Name + Type -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.name_label') }} <span class="text-danger">*</span></label>
                <input v-model="form.name" type="text" :placeholder="$t('contacts.name_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.name ? 'border-danger' : 'border-border']" />
                <p v-if="errors.name" class="text-xs text-danger mt-1">{{ errors.name }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.type_label') }} <span class="text-danger">*</span></label>
                <select v-model="form.type"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.type ? 'border-danger' : 'border-border']">
                  <option value="customer">{{ $t('contacts.type_customer') }}</option>
                  <option value="vendor">{{ $t('contacts.type_vendor') }}</option>
                  <option value="both">{{ $t('contacts.type_both') }}</option>
                </select>
                <p v-if="errors.type" class="text-xs text-danger mt-1">{{ errors.type }}</p>
              </div>
            </div>

            <!-- GSTIN + PAN -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.gstin_label') }}</label>
                <input v-model="form.gstin" type="text" :placeholder="$t('contacts.gstin_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors uppercase', errors.gstin ? 'border-danger' : 'border-border']" />
                <p v-if="errors.gstin" class="text-xs text-danger mt-1">{{ errors.gstin }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.pan_label') }}</label>
                <input v-model="form.pan" type="text" :placeholder="$t('contacts.pan_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors uppercase', errors.pan ? 'border-danger' : 'border-border']" />
                <p v-if="errors.pan" class="text-xs text-danger mt-1">{{ errors.pan }}</p>
              </div>
            </div>

            <!-- Mobile + Email -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.mobile_label') }}</label>
                <input v-model="form.mobile" type="tel" :placeholder="$t('contacts.mobile_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.mobile ? 'border-danger' : 'border-border']" />
                <p v-if="errors.mobile" class="text-xs text-danger mt-1">{{ errors.mobile }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.email_label') }}</label>
                <input v-model="form.email" type="email" :placeholder="$t('contacts.email_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.email ? 'border-danger' : 'border-border']" />
                <p v-if="errors.email" class="text-xs text-danger mt-1">{{ errors.email }}</p>
              </div>
            </div>

            <!-- Due In Days + Currency -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.due_in_days_label') }}</label>
                <input v-model.number="form.due_in_days" type="number" min="0" max="365" :placeholder="$t('contacts.due_in_days_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.currency_label') }}</label>
                <select v-model="form.currency"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                  <option value="INR">INR</option>
                  <option value="USD">USD</option>
                  <option value="EUR">EUR</option>
                  <option value="GBP">GBP</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Address Tab -->
          <div v-show="activeTab === 'address'" class="space-y-4">
            <h3 class="text-sm font-semibold text-foreground">{{ $t('contacts.billing_address') }}</h3>

            <!-- Address Line 1 + 2 -->
            <div class="space-y-3">
              <input v-model="form.billing_address1" type="text" :placeholder="$t('contacts.address1_placeholder')"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.billing_address1 ? 'border-danger' : 'border-border']" />
              <p v-if="errors.billing_address1" class="text-xs text-danger -mt-2">{{ errors.billing_address1 }}</p>
              <input v-model="form.billing_address2" type="text" :placeholder="$t('contacts.address2_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>

            <!-- City + Pincode + State -->
            <div class="grid grid-cols-3 gap-3">
              <div>
                <input v-model="form.billing_city" type="text" :placeholder="$t('contacts.city_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <div>
                <input v-model="form.billing_pincode" type="text" :placeholder="$t('contacts.pincode_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.billing_pincode ? 'border-danger' : 'border-border']" />
                <p v-if="errors.billing_pincode" class="text-xs text-danger mt-1">{{ errors.billing_pincode }}</p>
              </div>
              <div>
                <input v-model="form.billing_state" type="text" :placeholder="$t('contacts.state_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
            </div>

            <!-- Country -->
            <div>
              <input v-model="form.billing_country" type="text" :placeholder="$t('contacts.country_placeholder')"
                class="w-full max-w-xs px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>

            <!-- Shipping Same as Billing -->
            <div class="pt-4 border-t border-border">
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.shipping_same_as_billing" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                {{ $t('contacts.shipping_same') }}
              </label>
            </div>

            <!-- Shipping Address -->
            <div v-if="!form.shipping_same_as_billing" class="space-y-4">
              <h3 class="text-sm font-semibold text-foreground">{{ $t('contacts.shipping_address') }}</h3>

              <input v-model="form.shipping_address1" type="text" :placeholder="$t('contacts.address1_placeholder')"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.shipping_address1 ? 'border-danger' : 'border-border']" />
              <p v-if="errors.shipping_address1" class="text-xs text-danger -mt-2">{{ errors.shipping_address1 }}</p>

              <input v-model="form.shipping_address2" type="text" :placeholder="$t('contacts.address2_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />

              <div class="grid grid-cols-3 gap-3">
                <input v-model="form.shipping_city" type="text" :placeholder="$t('contacts.city_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.shipping_city ? 'border-danger' : 'border-border']" />
                <input v-model="form.shipping_pincode" type="text" :placeholder="$t('contacts.pincode_placeholder')"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.shipping_pincode ? 'border-danger' : 'border-border']" />
                <input v-model="form.shipping_state" type="text" :placeholder="$t('contacts.state_placeholder')"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <p v-if="errors.shipping_city" class="text-xs text-danger">{{ errors.shipping_city }}</p>

              <input v-model="form.shipping_country" type="text" :placeholder="$t('contacts.country_placeholder')"
                class="w-full max-w-xs px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>
          </div>

          <!-- Financial Tab -->
          <div v-show="activeTab === 'financial'" class="space-y-4">
            <!-- Opening Balance -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.opening_balance_label') }}</label>
                <div class="flex items-center gap-2 border border-border rounded-lg px-3 py-2 bg-card">
                  <span class="text-muted-foreground text-sm">₹</span>
                  <input v-model.number="form.opening_balance" type="number" min="0" step="0.01" :placeholder="$t('contacts.opening_balance_placeholder')"
                    class="flex-1 text-sm bg-transparent outline-none text-foreground" />
                </div>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('contacts.opening_balance_type_label') }}</label>
                <select v-model="form.opening_balance_type"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.opening_balance_type ? 'border-danger' : 'border-border']">
                  <option value="payable">{{ $t('contacts.payable') }}</option>
                  <option value="receivable">{{ $t('contacts.receivable') }}</option>
                </select>
                <p v-if="errors.opening_balance_type" class="text-xs text-danger mt-1">{{ errors.opening_balance_type }}</p>
              </div>
            </div>

            <!-- Enable Customer Portal -->
            <div>
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.enable_customer_portal" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                {{ $t('contacts.enable_portal') }}
              </label>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('common.notes') }}</label>
              <textarea v-model="form.notes" :placeholder="$t('contacts.notes_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" rows="3" maxlength="250" />
              <p class="text-xs text-muted-foreground text-right mt-0.5">{{ (form.notes || '').length }} / 250</p>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            {{ $t('common.cancel') }}
          </button>
          <button @click="saveContact" :disabled="saving"
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
import { useI18n } from 'vue-i18n';
import { useNotificationsStore } from '@/stores/notifications';
import { useContactsStore } from '@/stores/contacts';
import AppIcon from '@/components/ui/AppIcon.vue';

const { t } = useI18n();
const notify = useNotificationsStore();
const contactsStore = useContactsStore();

const saving = ref(false);
const showModal = ref(false);
const editId = ref(null);
const activeTab = ref('identity');

// DataTable state
const rows = computed(() => contactsStore.list);
const meta = computed(() => contactsStore.meta);
const loading = computed(() => contactsStore.loading);
const search = ref('');
const filterType = ref('');
const perPage = ref(10);
const sortCol = ref('name');
const sortDir = ref('asc');

let searchTimer = null;

const tabs = computed(() => [
  { key: 'identity', label: t('contacts.tab_identity') },
  { key: 'address', label: t('contacts.tab_address') },
  { key: 'financial', label: t('contacts.tab_financial') },
]);

const fetchDatatable = (page = 1) =>
  contactsStore.fetchList({
    page,
    per_page: perPage.value,
    search: search.value,
    type: filterType.value,
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

const sortIcons = computed(() => ({ name: sortCol.value !== 'name' ? '↕' : sortDir.value === 'asc' ? '↑' : '↓' }));

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

const typeClass = (type) => {
  const classes = { customer: 'status-active', vendor: 'status-onboarding', both: 'bg-purple-100 text-purple-700' };
  return classes[type] || 'status-active';
};

const typeLabel = (type) => {
  const labels = { customer: t('contacts.type_customer'), vendor: t('contacts.type_vendor'), both: t('contacts.type_both') };
  return labels[type] || type;
};

// Form
const defaultForm = () => ({
  name: '',
  type: 'customer',
  gstin: '',
  pan: '',
  mobile: '',
  email: '',
  due_in_days: 0,
  currency: 'INR',
  billing_address1: '',
  billing_address2: '',
  billing_city: '',
  billing_pincode: '',
  billing_state: '',
  billing_country: 'India',
  shipping_same_as_billing: 1,
  shipping_address1: '',
  shipping_address2: '',
  shipping_city: '',
  shipping_pincode: '',
  shipping_state: '',
  shipping_country: 'India',
  opening_balance: 0,
  opening_balance_type: 'payable',
  enable_customer_portal: 1,
  notes: '',
});

const form = reactive(defaultForm());
const errors = reactive({});

const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
const mobileRegex = /^[6-9]\d{9}$/;
const pincodeRegex = /^[1-9][0-9]{5}$/;

const contactSchema = yup.object().shape({
  name: yup.string().required(t('contacts.name_label') + ' is required.').min(2).max(255),
  type: yup.string().required().oneOf(['customer', 'vendor', 'both']),
  gstin: yup.string().nullable().matches(gstinRegex, 'Invalid GSTIN format (e.g. 22AAAAA0000A1Z5)'),
  pan: yup.string().nullable().matches(panRegex, 'Invalid PAN format (e.g. ABCDE1234F)'),
  mobile: yup.string().nullable().matches(mobileRegex, 'Invalid mobile number (10 digits starting with 6-9)'),
  email: yup.string().nullable().email('Invalid email address'),
  due_in_days: yup.number().min(0).max(365),
  currency: yup.string().length(3),
  billing_address1: yup.string().nullable().max(255),
  billing_pincode: yup.string().nullable().matches(pincodeRegex, 'Invalid pincode (6 digits)'),
  shipping_same_as_billing: yup.number().oneOf([0, 1]),
  shipping_address1: yup.string().when('shipping_same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping address is required.').max(255),
    otherwise: (s) => s.nullable(),
  }),
  shipping_city: yup.string().when('shipping_same_as_billing', {
    is: 0,
    then: (s) => s.required('Shipping city is required.'),
    otherwise: (s) => s.nullable(),
  }),
  shipping_pincode: yup.string().when('shipping_same_as_billing', {
    is: 0,
    then: (s) => s.nullable().matches(pincodeRegex, 'Invalid pincode (6 digits)'),
    otherwise: (s) => s.nullable(),
  }),
  opening_balance: yup.number().min(0).max(999999999.99),
  opening_balance_type: yup.string().when('opening_balance', {
    is: (v) => v > 0,
    then: (s) => s.required('Balance type is required.').oneOf(['payable', 'receivable']),
    otherwise: (s) => s.nullable(),
  }),
  notes: yup.string().nullable().max(250),
});

const validateForm = async () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await contactSchema.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner?.forEach((e) => { if (e.path) errors[e.path] = e.message; });
    return false;
  }
};

const openModal = (contact = null) => {
  editId.value = contact?.id ?? null;
  Object.assign(form, defaultForm(), contact ?? {});
  Object.keys(errors).forEach((k) => delete errors[k]);
  activeTab.value = 'identity';
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const saveContact = async () => {
  if (!await validateForm()) return;
  saving.value = true;
  try {
    if (editId.value) {
      await contactsStore.updateContact(editId.value, form);
      notify.showSuccess(t('contacts.updated'), t('contacts.saved_success', { name: form.name }));
    } else {
      await contactsStore.createContact(form);
      notify.showSuccess(t('contacts.created'), t('contacts.saved_success', { name: form.name }));
    }
    closeModal();
  } catch (e) {
    const backendErrors = e.response?.data?.errors;
    if (backendErrors) {
      Object.entries(backendErrors).forEach(([k, v]) => { errors[k] = v[0]; });
    } else {
      notify.showError(t('common.save_failed'), e.response?.data?.message || t('common.something_wrong'));
    }
  } finally {
    saving.value = false;
  }
};

const deleteContact = async (contact) => {
  if (!confirm(t('common.confirm_delete', { name: contact.name }))) return;
  try {
    await contactsStore.deleteContact(contact.id);
    notify.showSuccess(t('contacts.deleted'), t('contacts.deleted_success', { name: contact.name }));
  } catch {
    notify.showError(t('common.delete_failed'), t('common.could_not_delete', { resource: t('contacts.resource') }));
  }
};

onMounted(reload);
</script>
