<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">{{ $t('items.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $t('items.subtitle') }}</p>
      </div>
      <div class="flex items-center gap-2">
        <router-link to="/system-setup" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          {{ $t('common.system_setup') }}
        </router-link>
        <button @click="exportItems" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          <AppIcon name="download" :size="15" /> {{ $t('common.export') }}
        </button>
        <button @click="$refs.importInput.click()" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          <AppIcon name="upload" :size="15" /> {{ $t('common.import') }}
        </button>
        <input ref="importInput" type="file" accept=".xlsx,.xls" class="hidden" @change="handleImport" />
        <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
          {{ $t('items.new') }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" :placeholder="$t('common.search')"
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterType" @change="reload"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">{{ $t('common.all_types') }}</option>
        <option value="product">{{ $t('items.filter_product') }}</option>
        <option value="service">{{ $t('items.filter_service') }}</option>
        <option value="charge">{{ $t('items.filter_charge') }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th @click="setSort('name')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_name') }} <span class="ml-1">{{ sortIcons['name'] }}</span>
            </th>
            <th @click="setSort('item_type')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_type') }} <span class="ml-1">{{ sortIcons['item_type'] }}</span>
            </th>
            <th @click="setSort('sku')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_sku') }} <span class="ml-1">{{ sortIcons['sku'] }}</span>
            </th>
            <th @click="setSort('category')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_category') }} <span class="ml-1">{{ sortIcons['category'] }}</span>
            </th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('items.col_unit') }}</th>
            <th @click="setSort('sale_price')" class="text-right px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_sale_price') }} <span class="ml-1">{{ sortIcons['sale_price'] }}</span>
            </th>
            <th @click="setSort('opening_stock_qty')" class="text-right px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('items.col_stock') }} <span class="ml-1">{{ sortIcons['opening_stock_qty'] }}</span>
            </th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="8" class="text-center py-10 text-muted-foreground">{{ $t('common.loading') }}</td></tr>
          <tr v-else-if="!rows.length"><td colspan="8" class="text-center py-10 text-muted-foreground">{{ $t('common.no_data', { resource: $t('items.resource') }) }}</td></tr>
          <tr v-for="item in rows" :key="item.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 font-medium text-foreground">
              {{ item.name }}
              <p v-if="item.short_description" class="text-xs text-muted-foreground">{{ item.short_description }}</p>
            </td>
            <td class="px-4 py-3">
              <span :class="item.item_type === 'product' ? 'status-active' : 'status-onboarding'" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ item.item_type }}
              </span>
            </td>
            <td class="px-4 py-3 text-muted-foreground font-mono text-xs">{{ item.sku }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ item.category || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ item.unit }}</td>
            <td class="px-4 py-3 text-right font-medium">₹{{ formatNum(item.sale_price) }}</td>
            <td class="px-4 py-3 text-right text-muted-foreground">{{ item.opening_stock_qty }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(item)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="duplicateItem(item)" type="button" title="Duplicate"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="copy" :size="15" />
                </button>
                <button @click="deleteItem(item)" type="button" title="Delete"
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
          {{ $t('common.showing', { from: meta.from, to: meta.to, total: meta.total, resource: $t('items.resource') }) }}
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
      <div class="bg-card rounded-2xl shadow-card-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? $t('items.edit_modal') : isDuplicate ? $t('items.duplicate_modal') : $t('items.new_modal') }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="px-6 py-5 space-y-5">
          <!-- Row 1: Item Type + Checkboxes -->
          <div class="flex items-start gap-6">
            <div class="w-56">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('items.item_type') }}</label>
              <select v-model="form.item_type" @change="handleItemTypeChange" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="product">{{ $t('items.filter_product') }}</option>
                <option value="service">{{ $t('items.filter_service') }}</option>
                <option value="charge">{{ $t('items.filter_charge') }}</option>
              </select>
            </div>
            <div v-if="isProduct" class="flex flex-col gap-2 pt-5">
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.manage_inventory" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                {{ $t('items.manage_inventory') }}
              </label>
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.serialized_product" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                {{ $t('items.serialized_product') }}
              </label>
            </div>
          </div>

          <!-- Name -->
          <div v-if="isProduct">
            <input v-model="form.name" @change="form.validate('name')" type="text" placeholder="Name*"
              :class="['w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.name ? 'border-danger' : '']" />
            <p v-if="errors.name" class="text-xs text-danger mt-1">{{ errors.name }}</p>
          </div>

          <!-- Name + SAC -->
          <div v-else class="grid grid-cols-2 gap-3">
            <div>
              <input v-model="form.name" @change="form.validate('name')" type="text" placeholder="Name*"
                :class="['w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.name ? 'border-danger' : '']" />
              <p v-if="errors.name" class="text-xs text-danger mt-1">{{ errors.name }}</p>
            </div>
            <input v-model="form.hsn" type="text" :placeholder="$t('items.sac')" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- HSN + SKU -->
          <div v-if="isProduct" class="grid grid-cols-2 gap-3">
            <input v-model="form.hsn" type="text" :placeholder="$t('items.hsn')" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            <input v-model="form.sku" type="text" :placeholder="$t('items.item_code')" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- Unit + Tax Category -->
          <div v-if="!isCharge" class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('items.unit_of_measurement') }}</label>
              <select v-model="form.unit" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
              </select>
              <p v-if="form.serialized_product" class="text-xs text-muted-foreground mt-1">{{ $t('items.serialized_uom_note') }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('items.tax_category') }}</label>
              <select v-model="form.tax_category" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="">{{ $t('common.none') }}</option>
                <option v-for="t in taxCategories" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
          </div>

          <!-- Category + Stock Category -->
          <div v-if="isProduct" class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('items.stock_category') }}</label>
              <select v-model="form.stock_category" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="">{{ $t('common.none') }}</option>
                <option v-for="c in stockCategories" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>
          </div>

          <!-- Invoice Description -->
          <div v-if="!isCharge">
            <textarea v-model="form.invoice_description" :placeholder="$t('items.invoice_description_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" rows="2" maxlength="4000" />
            <div class="flex items-center justify-between mt-0.5 text-xs text-muted-foreground">
              <p>{{ $t('items.invoice_description_hint') }}</p>
              <p>{{ (form.invoice_description || '').length }} / 4000</p>
            </div>
          </div>

          <!-- Sales Price -->
          <div>
            <p class="text-sm font-semibold text-foreground mb-2">{{ $t('items.sales_price') }}</p>
            <div class="flex gap-3 items-center">
              <div class="flex shrink-0 items-center gap-1 border border-border rounded-lg px-3 py-2 bg-card">
                <span class="text-muted-foreground text-sm">₹</span>
                <input v-model="form.sale_price" @input="handleSalePriceInput" type="number" min="0" step="0.01" placeholder="0.00"
                  class="w-24 text-sm bg-transparent outline-none text-foreground" />
              </div>
              <select v-model="form.sale_price_type" class="w-36 shrink-0 px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="with_tax">{{ $t('items.with_tax') }}</option>
                <option value="without_tax">{{ $t('items.without_tax') }}</option>
              </select>
              <div class="flex min-w-48 flex-1 items-center gap-2 border border-border rounded-lg px-3 py-2 bg-card">
                <input v-model="form.sale_discount" @input="handleSaleDiscountInput" type="number" min="0" :max="form.sale_discount_type === 'percent' ? 100 : null" step="0.01" :placeholder="$t('items.discount')"
                  class="min-w-0 flex-1 text-sm bg-transparent outline-none text-foreground" />
                <button type="button" @click="toggleSaleDiscountType"
                  class="shrink-0 text-xs font-medium text-primary border border-primary rounded px-2 py-0.5">
                  {{ form.sale_discount_type === 'percent' ? '%' : '₹' }}
                </button>
              </div>
            </div>
            <p v-if="errors.sale_discount" class="text-xs text-danger mt-1">{{ errors.sale_discount }}</p>
            <p class="text-xs text-muted-foreground mt-1">
              {{ discountLabel }}
            </p>
          </div>

          <!-- Opening Stock (only for products with inventory) -->
          <div v-if="isProduct && form.manage_inventory">
            <p class="text-sm font-semibold text-foreground mb-2">{{ $t('items.opening_stock') }}</p>
            <div class="flex gap-3">
              <div class="flex items-center gap-2 border border-border rounded-lg px-3 py-2 bg-card">
                <input v-model="form.opening_stock_qty" type="number" min="0" step="0.001" :placeholder="$t('items.qty')"
                  class="w-20 text-sm bg-transparent outline-none text-foreground" />
                <span class="text-xs text-muted-foreground">{{ form.unit }}</span>
              </div>
              <div class="flex items-center gap-1 border border-border rounded-lg px-3 py-2 bg-card flex-1">
                <span class="text-muted-foreground text-sm">₹</span>
                <input v-model="form.opening_stock_cost" type="number" min="0" step="0.01" :placeholder="$t('items.cost_per_qty')"
                  class="w-full text-sm bg-transparent outline-none text-foreground" />
              </div>
            </div>

            <!-- Serial Numbers (only for serialized) -->
            <div v-if="form.serialized_product" class="mt-3">
              <textarea v-model="form.serial_numbers" :placeholder="$t('items.serial_numbers_placeholder')"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" rows="3" />
              <p class="text-xs text-muted-foreground text-right mt-0.5">
                {{ serialCount }} / {{ form.opening_stock_qty || 0 }}
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-border">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            {{ $t('common.cancel') }}
          </button>
          <button @click="saveItem" :disabled="saving"
            class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? $t('common.saving') : (editId ? $t('common.update') : $t('common.create')) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useForm } from 'laravel-precognition-vue';
import * as XLSX from 'xlsx';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';
import { api } from '@/services/api';
import AppIcon from '@/components/ui/AppIcon.vue';

const notify = useNotificationsStore();
const resourcesStore = useResourcesStore();

const saving      = ref(false);
const showModal   = ref(false);
const editId      = ref(null);
const isDuplicate = ref(false);
const loading     = ref(false);

// DataTable state
const rows       = ref([]);
const search     = ref('');
const filterType = ref('');
const perPage    = ref(10);
const sortCol    = ref('name');
const sortDir    = ref('asc');
const meta       = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });

let searchTimer = null;

const COLUMNS = ['name', 'item_type', 'sku', 'category', 'unit', 'sale_price', 'opening_stock_qty'];

const fetchDatatable = async (page = 1) => {
  loading.value = true;
  try {
    const colIndex = COLUMNS.indexOf(sortCol.value);
    const { data } = await api.get('/items/datatable', {
      params: {
        draw: 1,
        start: (page - 1) * perPage.value,
        length: perPage.value,
        'search[value]': search.value,
        'order[0][column]': colIndex >= 0 ? colIndex : 0,
        'order[0][dir]': sortDir.value,
        item_type: filterType.value,
        ...COLUMNS.reduce((acc, col, i) => {
          acc[`columns[${i}][data]`] = col;
          acc[`columns[${i}][searchable]`] = 'true';
          acc[`columns[${i}][orderable]`] = 'true';
          return acc;
        }, {}),
      },
    });
    rows.value = data.data;
    const total = data.recordsFiltered;
    const lastPage = Math.max(1, Math.ceil(total / perPage.value));
    const from = total ? (page - 1) * perPage.value + 1 : 0;
    const to = Math.min(page * perPage.value, total);
    meta.value = { current_page: page, last_page: lastPage, from, to, total };
  } finally {
    loading.value = false;
  }
};

const reload = () => fetchDatatable(1);

const onSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(reload, 350);
};

const setSort = (col) => {
  if (sortCol.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortCol.value = col;
    sortDir.value = 'asc';
  }
  reload();
};

const sortIcons = computed(() => {
  const icons = {};
  COLUMNS.forEach(col => {
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

const units = computed(() => resourcesStore.setupOptions.units);
const taxCategories = computed(() => resourcesStore.setupOptions.taxCategories);
const stockCategories = computed(() => resourcesStore.setupOptions.stockCategories);

const defaultForm = () => ({
  item_type: 'product',
  manage_inventory: 1,
  serialized_product: 0,
  name: '',
  hsn: '',
  sku: '',
  category: '',
  unit: 'Pcs',
  tax_category: '',
  stock_category: '',
  short_description: '',
  invoice_description: '',
  sale_price: 0,
  sale_price_type: 'with_tax',
  sale_discount: 0,
  sale_discount_type: 'percent',
  purchase_price: 0,
  opening_stock_qty: 0,
  opening_stock_cost: 0,
  serial_numbers: '',
});

const form = useForm(
  () => (editId.value ? 'put' : 'post'),
  () => (editId.value ? `/items/${editId.value}` : '/items'),
  defaultForm()
);

form.setValidationTimeout(300);

const errors = computed(() => form.errors);
const isProduct = computed(() => form.item_type === 'product');
const isService = computed(() => form.item_type === 'service');
const isCharge  = computed(() => form.item_type === 'charge');

const serialCount = computed(() => {
  if (!form.serial_numbers) return 0;
  return form.serial_numbers.split('\n').filter(s => s.trim()).length;
});

const formatNum = (n) => Number(n || 0).toFixed(2);

const saleDiscount = computed(() => Math.max(Number(form.sale_discount || 0), 0));
const discountLabel = computed(() => (
  form.sale_discount_type === 'percent'
    ? `${formatNum(saleDiscount.value)}%`
    : `₹${formatNum(saleDiscount.value)}`
));

const applyItemTypeDefaults = () => {
  if (isProduct.value) { form.unit ||= 'Pcs'; return; }
  form.manage_inventory = 0; form.serialized_product = 0;
  form.sku = ''; form.stock_category = ''; form.category = '';
  form.short_description = ''; form.purchase_price = 0;
  form.opening_stock_qty = 0; form.opening_stock_cost = 0; form.serial_numbers = '';
  if (isService.value) { form.unit ||= 'Pcs'; return; }
  form.unit = 'Pcs'; form.tax_category = ''; form.invoice_description = '';
};

const handleItemTypeChange = () => { applyItemTypeDefaults(); form.validate('item_type'); };

const normalizeSaleDiscount = () => {
  const d = Number(form.sale_discount || 0);
  if (d < 0) form.sale_discount = 0;
  if (form.sale_discount_type === 'percent' && d > 100) form.sale_discount = 100;
};

const handleSalePriceInput = () => {
  if (Number(form.sale_price || 0) < 0) form.sale_price = 0;
  form.validate('sale_price'); form.validate('sale_discount');
};

const handleSaleDiscountInput = () => { normalizeSaleDiscount(); form.validate('sale_discount'); };

const toggleSaleDiscountType = () => {
  form.sale_discount_type = form.sale_discount_type === 'percent' ? 'amount' : 'percent';
  normalizeSaleDiscount(); form.validate('sale_discount');
};

const openModal = (item = null) => {
  isDuplicate.value = false;
  if (item) {
    editId.value = item.id;
    form.reset();
    form.setData({ ...defaultForm(), ...item });
  } else {
    editId.value = null;
    form.reset();
    form.setData(defaultForm());
  }
  applyItemTypeDefaults();
  form.setErrors({});
  showModal.value = true;
};

const duplicateItem = (item) => {
  isDuplicate.value = true;
  editId.value = null;
  form.reset();
  form.setData({ ...defaultForm(), ...item, id: undefined, sku: '', name: `Copy of ${item.name}`, opening_stock_qty: 0, opening_stock_cost: 0, serial_numbers: '' });
  applyItemTypeDefaults();
  form.setErrors({});
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const saveItem = async () => {
  applyItemTypeDefaults();
  normalizeSaleDiscount();
  saving.value = true;
  try {
    await form.submit();
    notify.showSuccess(editId.value ? 'Item Updated' : 'Item Created', `"${form.name}" saved successfully.`);
    await reload();
    closeModal();
  } catch (e) {
    if (!e.response?.data?.errors) notify.showError('Save Failed', e.response?.data?.message || 'Something went wrong.');
  } finally {
    saving.value = false;
  }
};

const deleteItem = async (item) => {
  if (!confirm(`Delete "${item.name}"?`)) return;
  try {
    await resourcesStore.deleteItem(item);
    notify.showSuccess('Item Deleted', `"${item.name}" has been deleted.`);
    await reload();
  } catch {
    notify.showError('Delete Failed', 'Could not delete the item.');
  }
};

const IMPORT_COLUMNS = [
  'name', 'item_type', 'hsn', 'sku', 'category', 'unit',
  'tax_category', 'stock_category', 'short_description', 'invoice_description',
  'sale_price', 'sale_price_type', 'sale_discount', 'sale_discount_type',
  'purchase_price', 'manage_inventory', 'opening_stock_qty', 'opening_stock_cost',
];

const exportItems = async () => {
  const { data } = await api.get('/items/datatable', {
    params: { draw: 1, start: 0, length: -1, 'search[value]': search.value, item_type: filterType.value,
      ...COLUMNS.reduce((acc, col, i) => { acc[`columns[${i}][data]`] = col; return acc; }, {}) },
  });
  const rowsAll = data.data;
  const ws = XLSX.utils.json_to_sheet(
    rowsAll.map(item => IMPORT_COLUMNS.reduce((acc, col) => { acc[col] = item[col] ?? ''; return acc; }, {}))
  );
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Items');
  XLSX.writeFile(wb, 'items.xlsx');
};

const handleImport = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  e.target.value = '';
  const data = await file.arrayBuffer();
  const wb = XLSX.read(data);
  const importRows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { defval: '' });
  if (!importRows.length) { notify.showError('Import Failed', 'No data found in the file.'); return; }
  try {
    const res = await api.post('/items/import', { items: importRows });
    notify.showSuccess('Import Successful', `${res.data.imported} item(s) imported.`);
    await reload();
  } catch (err) {
    notify.showError('Import Failed', err.response?.data?.message || 'Something went wrong.');
  }
};

onMounted(() => { resourcesStore.fetchSystemSettings(); reload(); });
</script>
