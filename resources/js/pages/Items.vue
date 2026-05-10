<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">Items</h1>
        <p class="text-sm text-muted-foreground">Manage your products and services</p>
      </div>
      <div class="flex items-center gap-2">
        <router-link to="/system-setup" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          ⚙ System Setup
        </router-link>
        <button @click="exportItems" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          <AppIcon name="download" :size="15" /> Export
        </button>
        <button @click="$refs.importInput.click()" class="flex items-center gap-2 px-4 py-2 border border-border text-sm font-medium rounded-lg text-foreground hover:bg-muted transition-colors">
          <AppIcon name="upload" :size="15" /> Import
        </button>
        <input ref="importInput" type="file" accept=".xlsx,.xls" class="hidden" @change="handleImport" />
        <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
          + New Item
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="fetchItems" type="text" placeholder="Search items..."
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterType" @change="fetchItems"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">All Types</option>
        <option value="product">Product</option>
        <option value="service">Service</option>
        <option value="charge">Charge</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Name</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Type</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">SKU</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Category</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Unit</th>
            <th class="text-right px-4 py-3 text-muted-foreground font-medium">Sale Price</th>
            <th class="text-right px-4 py-3 text-muted-foreground font-medium">Stock</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="8" class="text-center py-10 text-muted-foreground">Loading...</td></tr>
          <tr v-else-if="!items.length"><td colspan="8" class="text-center py-10 text-muted-foreground">No items found</td></tr>
          <tr v-for="item in items" :key="item.id" class="border-t border-border hover:bg-muted/30 transition-colors">
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
                <button
                  @click="openModal(item)"
                  type="button"
                  title="Edit"
                  aria-label="Edit item"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                  <AppIcon name="edit" :size="15" />
                </button>
                <button
                  @click="deleteItem(item)"
                  type="button"
                  title="Delete"
                  aria-label="Delete item"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-danger/10 text-danger transition-colors hover:bg-danger hover:text-white focus:outline-none focus:ring-2 focus:ring-danger/30"
                >
                  <AppIcon name="trash-2" :size="15" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-card rounded-2xl shadow-card-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? 'Edit Item' : 'New Item' }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="px-6 py-5 space-y-5">
          <!-- Row 1: Item Type + Checkboxes -->
          <div class="flex items-start gap-6">
            <div class="w-56">
              <label class="block text-xs text-muted-foreground mb-1">Item Type</label>
              <select v-model="form.item_type" @change="handleItemTypeChange" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="product">Product</option>
                <option value="service">Service</option>
                <option value="charge">Charge</option>
              </select>
            </div>
            <div v-if="isProduct" class="flex flex-col gap-2 pt-5">
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.manage_inventory" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                Manage Inventory / Stock
              </label>
              <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
                <input type="checkbox" v-model="form.serialized_product" :true-value="1" :false-value="0"
                  class="w-4 h-4 accent-primary" />
                Serialized Product
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
            <input v-model="form.hsn" type="text" placeholder="SAC" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- HSN + SKU -->
          <div v-if="isProduct" class="grid grid-cols-2 gap-3">
            <input v-model="form.hsn" type="text" placeholder="HSN" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            <input v-model="form.sku" type="text" placeholder="Item Code" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>

          <!-- Unit + Tax Category -->
          <div v-if="!isCharge" class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Unit of Measurement*</label>
              <select v-model="form.unit" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
              </select>
              <p v-if="form.serialized_product" class="text-xs text-muted-foreground mt-1">For Serialized Products UOM with 0 Precisions Can be Set.</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Tax Category</label>
              <select v-model="form.tax_category" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="">None</option>
                <option v-for="t in taxCategories" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
          </div>

          <!-- Category + Stock Category -->
          <div v-if="isProduct" class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="block text-xs text-muted-foreground mb-1">Stock Category</label>
              <select v-model="form.stock_category" class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="">None</option>
                <option v-for="c in stockCategories" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>
          </div>

          <!-- Invoice Description -->
          <div v-if="!isCharge">
            <textarea v-model="form.invoice_description" placeholder="Default Invoice Description"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" rows="2" maxlength="4000" />
            <div class="flex items-center justify-between mt-0.5 text-xs text-muted-foreground">
              <p>Shown on invoice e.g. "3 years warranty"</p>
              <p>{{ (form.invoice_description || '').length }} / 4000</p>
            </div>
          </div>

          <!-- Sales Price -->
          <div>
            <p class="text-sm font-semibold text-foreground mb-2">Sales Price</p>
            <div class="flex gap-3 items-center">
              <div class="flex shrink-0 items-center gap-1 border border-border rounded-lg px-3 py-2 bg-card">
                <span class="text-muted-foreground text-sm">₹</span>
                <input v-model="form.sale_price" @input="handleSalePriceInput" type="number" min="0" step="0.01" placeholder="0.00"
                  class="w-24 text-sm bg-transparent outline-none text-foreground" />
              </div>
              <select v-model="form.sale_price_type" class="w-36 shrink-0 px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="with_tax">With tax</option>
                <option value="without_tax">Without tax</option>
              </select>
              <div class="flex min-w-48 flex-1 items-center gap-2 border border-border rounded-lg px-3 py-2 bg-card">
                <input v-model="form.sale_discount" @input="handleSaleDiscountInput" type="number" min="0" :max="form.sale_discount_type === 'percent' ? 100 : null" step="0.01" placeholder="Discount"
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
            <p class="text-sm font-semibold text-foreground mb-2">Opening Stock</p>
            <div class="flex gap-3">
              <div class="flex items-center gap-2 border border-border rounded-lg px-3 py-2 bg-card">
                <input v-model="form.opening_stock_qty" type="number" min="0" step="0.001" placeholder="Qty"
                  class="w-20 text-sm bg-transparent outline-none text-foreground" />
                <span class="text-xs text-muted-foreground">{{ form.unit }}</span>
              </div>
              <div class="flex items-center gap-1 border border-border rounded-lg px-3 py-2 bg-card flex-1">
                <span class="text-muted-foreground text-sm">₹</span>
                <input v-model="form.opening_stock_cost" type="number" min="0" step="0.01" placeholder="Cost per qty"
                  class="w-full text-sm bg-transparent outline-none text-foreground" />
              </div>
            </div>

            <!-- Serial Numbers (only for serialized) -->
            <div v-if="form.serialized_product" class="mt-3">
              <textarea v-model="form.serial_numbers" placeholder="Serial Numbers (one per line)"
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
            CANCEL
          </button>
          <button @click="saveItem" :disabled="saving"
            class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? 'SAVING...' : (editId ? 'UPDATE' : 'CREATE') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useForm } from 'laravel-precognition-vue';
import * as XLSX from 'xlsx';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';
import { api } from '@/services/api';
import AppIcon from '@/components/ui/AppIcon.vue';

const notify = useNotificationsStore();
const resourcesStore = useResourcesStore();

const saving    = ref(false);
const showModal = ref(false);
const editId    = ref(null);
const search    = ref('');
const filterType = ref('');

const itemQuery = computed(() => ({ search: search.value, item_type: filterType.value }));
const items = computed(() => resourcesStore.getItems(itemQuery.value));
const loading = computed(() => resourcesStore.isItemsLoading(itemQuery.value));

const units = computed(() => resourcesStore.setupOptions.units);
const taxCategories = computed(() => resourcesStore.setupOptions.taxCategories);
const stockCategories = computed(() => resourcesStore.setupOptions.stockCategories);

const fetchSetupOptions = () => resourcesStore.fetchSystemSettings();

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
const isCharge = computed(() => form.item_type === 'charge');

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

const fetchItems = async () => {
  await resourcesStore.fetchItems(itemQuery.value);
};

const applyItemTypeDefaults = () => {
  if (isProduct.value) {
    form.unit ||= 'Pcs';
    return;
  }

  form.manage_inventory = 0;
  form.serialized_product = 0;
  form.sku = '';
  form.stock_category = '';
  form.category = '';
  form.short_description = '';
  form.purchase_price = 0;
  form.opening_stock_qty = 0;
  form.opening_stock_cost = 0;
  form.serial_numbers = '';

  if (isService.value) {
    form.unit ||= 'Pcs';
    return;
  }

  form.unit = 'Pcs';
  form.tax_category = '';
  form.invoice_description = '';
};

const handleItemTypeChange = () => {
  applyItemTypeDefaults();
  form.validate('item_type');
};

const normalizeSaleDiscount = () => {
  const discount = Number(form.sale_discount || 0);
  if (discount < 0) form.sale_discount = 0;
  if (form.sale_discount_type === 'percent' && discount > 100) {
    form.sale_discount = 100;
  }
};

const handleSalePriceInput = () => {
  if (Number(form.sale_price || 0) < 0) form.sale_price = 0;
  form.validate('sale_price');
  form.validate('sale_discount');
};

const handleSaleDiscountInput = () => {
  normalizeSaleDiscount();
  form.validate('sale_discount');
};

const toggleSaleDiscountType = () => {
  form.sale_discount_type = form.sale_discount_type === 'percent' ? 'amount' : 'percent';
  normalizeSaleDiscount();
  form.validate('sale_discount');
};

const openModal = (item = null) => {
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

const closeModal = () => { showModal.value = false; };

const saveItem = async () => {
  applyItemTypeDefaults();
  normalizeSaleDiscount();
  saving.value = true;
  try {
    await form.submit();

    if (editId.value) {
      notify.showSuccess('Item Updated', `"${form.name}" has been updated successfully.`);
    } else {
      notify.showSuccess('Item Created', `"${form.name}" has been created successfully.`);
    }

    await resourcesStore.fetchItems(itemQuery.value, { force: true });
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

const exportItems = () => {
  const rows = items.value.map(item =>
    IMPORT_COLUMNS.reduce((acc, col) => { acc[col] = item[col] ?? ''; return acc; }, {})
  );
  if (!rows.length) {
    rows.push(IMPORT_COLUMNS.reduce((acc, col) => { acc[col] = ''; return acc; }, {}));
  }
  const ws = XLSX.utils.json_to_sheet(rows);
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
  const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { defval: '' });

  if (!rows.length) {
    notify.showError('Import Failed', 'No data found in the file.');
    return;
  }

  try {
    const res = await api.post('/items/import', { items: rows });
    notify.showSuccess('Import Successful', `${res.data.imported} item(s) imported.`);
    await resourcesStore.fetchItems(itemQuery.value, { force: true });
  } catch (err) {
    notify.showError('Import Failed', err.response?.data?.message || 'Something went wrong.');
  }
};

onMounted(() => { fetchSetupOptions(); fetchItems(); });
</script>
