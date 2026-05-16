<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <button @click="router.back()" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-muted text-muted-foreground hover:bg-primary/10 hover:text-primary transition-colors">
          <AppIcon name="arrow-left" :size="16" />
        </button>
        <div>
          <h1 class="text-xl font-semibold text-foreground">{{ $t('purchases.create_title') }}</h1>
          <p class="text-sm text-muted-foreground">{{ $t('purchases.create_subtitle') }}</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="savePurchase" class="space-y-5">
      <!-- Invoice Details -->
      <div class="bg-card border border-border rounded-xl p-5">
        <h2 class="text-sm font-semibold text-foreground mb-4">{{ $t('purchases.invoice_details') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.invoice_date') }} <span class="text-danger">*</span></label>
            <input v-model="form.date" type="date"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.date ? 'border-danger' : 'border-border']" />
            <p v-if="errors.date" class="text-xs text-danger mt-1">{{ errors.date }}</p>
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.due_date') }} <span class="text-danger">*</span></label>
            <input v-model="form.due_date" type="date"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.due_date ? 'border-danger' : 'border-border']" />
            <p v-if="errors.due_date" class="text-xs text-danger mt-1">{{ errors.due_date }}</p>
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.vendor') }} <span class="text-danger">*</span></label>
            <select v-model="form.contact_id"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.contact_id ? 'border-danger' : 'border-border']">
              <option value="">{{ $t('purchases.select_vendor') }}</option>
              <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">{{ vendor.billing_name }} ({{ vendor.mobile_number }})</option>
            </select>
            <p v-if="errors.contact_id" class="text-xs text-danger mt-1">{{ errors.contact_id }}</p>
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.warehouse') }}</label>
            <select v-model="form.warehouse"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
              <option value="">{{ $t('purchases.select_warehouse') }}</option>
              <option v-for="wh in warehouses" :key="wh.id" :value="wh.name">{{ wh.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.payment_terms') }}</label>
            <input v-model="form.payment_terms" type="text" :placeholder="$t('purchases.payment_terms_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.notes') }}</label>
            <textarea v-model="form.notes" rows="2" :placeholder="$t('purchases.notes_placeholder')"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors resize-none" />
          </div>
        </div>
      </div>

      <!-- Invoice Items -->
      <div class="bg-card border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-semibold text-foreground">{{ $t('purchases.invoice_items') }}</h2>
          <button type="button" @click="addItem"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
            <AppIcon name="plus" :size="14" /> {{ $t('purchases.add_item') }}
          </button>
        </div>

        <!-- Empty State -->
        <div v-if="!form.items.length" :class="['text-center py-10 text-sm border border-dashed rounded-lg', errors.items ? 'border-danger text-danger' : 'border-border text-muted-foreground']">
          {{ errors.items || $t('purchases.no_items') }}
        </div>

        <!-- Repeater Items -->
        <div class="space-y-3">
          <div v-for="(item, index) in form.items" :key="index"
            class="border border-border rounded-lg p-4 bg-muted/20">

            <div class="flex items-center justify-between mb-3">
              <span class="text-xs font-semibold text-muted-foreground">{{ $t('purchases.item_no', { n: index + 1 }) }}</span>
              <button type="button" @click="removeItem(index)"
                class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-danger/10 text-danger transition-colors hover:bg-danger hover:text-white focus:outline-none">
                <AppIcon name="trash-2" :size="13" />
              </button>
            </div>

            <div class="mb-3">
              <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.product') }} <span class="text-danger">*</span></label>
              <select v-model="item.item_id" @change="selectItem(index, $event)"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', itemErrors[index]?.item_id ? 'border-danger' : 'border-border']">
                <option value="">{{ $t('purchases.select_product') }}</option>
                <option v-for="product in items.filter(p => p)" :key="product.id" :value="product.id">{{ product.name }}</option>
              </select>
              <p v-if="itemErrors[index]?.item_id" class="text-xs text-danger mt-1">{{ itemErrors[index].item_id }}</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.qty') }} <span class="text-danger">*</span></label>
                <input v-model.number="item.qty" @input="calculateItemTotal(index)" type="number" min="1" step="1"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', itemErrors[index]?.qty ? 'border-danger' : 'border-border']" />
                <p v-if="itemErrors[index]?.qty" class="text-xs text-danger mt-1">{{ itemErrors[index].qty }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.unit_price') }} <span class="text-danger">*</span></label>
                <input v-model.number="item.unit_price" @input="calculateItemTotal(index)" type="number" min="0" step="0.01"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', itemErrors[index]?.unit_price ? 'border-danger' : 'border-border']" />
                <p v-if="itemErrors[index]?.unit_price" class="text-xs text-danger mt-1">{{ itemErrors[index].unit_price }}</p>
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.discount_pct') }}</label>
                <input v-model.number="item.discount" @input="calculateItemTotal(index)" type="number" min="0" max="100" step="0.01"
                  class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
              </div>
              <div>
                <label class="block text-xs text-muted-foreground mb-1">{{ $t('purchases.total') }}</label>
                <div class="px-3 py-2 bg-muted border border-border rounded-lg text-sm font-semibold text-foreground">
                  ₹{{ formatNum(item.total || 0) }}
                </div>
              </div>
            </div>

            <div v-if="item.taxes?.length" class="mt-3 flex flex-wrap gap-1">
              <span class="text-xs text-muted-foreground mr-1">{{ $t('purchases.tax_label') }}</span>
              <span v-for="tax in item.taxes" :key="tax" class="status-onboarding px-2 py-0.5 rounded-full text-xs font-medium">{{ tax }}</span>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div v-if="form.items.length" class="flex justify-end mt-5 pt-4 border-t border-border">
          <div class="w-64 space-y-2 text-sm">
            <div class="flex justify-between text-muted-foreground"><span>{{ $t('purchases.subtotal') }}</span><span>₹{{ formatNum(totals.subtotal) }}</span></div>
            <div class="flex justify-between text-muted-foreground"><span>{{ $t('purchases.discount') }}</span><span>₹{{ formatNum(totals.discount) }}</span></div>
            <div class="flex justify-between text-muted-foreground"><span>{{ $t('purchases.tax') }}</span><span>₹{{ formatNum(totals.tax) }}</span></div>
            <div class="flex justify-between font-semibold text-foreground border-t border-border pt-2"><span>{{ $t('purchases.total') }}</span><span>₹{{ formatNum(totals.total) }}</span></div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3">
        <button type="button" @click="router.back()"
          class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
          {{ $t('common.cancel') }}
        </button>
        <button type="submit" :disabled="loading"
          class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
          {{ loading ? $t('purchases.creating') : $t('common.create') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useApi } from '@/composables/useApi';
import { api } from '@/services/api';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';
import AppIcon from '@/components/ui/AppIcon.vue';
import * as yup from 'yup';
import { useVendorsStore } from '@/stores/vendors';

const router = useRouter();
const { t } = useI18n();
const notify = useNotificationsStore();
const { loading, execute } = useApi();
const resourcesStore = useResourcesStore();
const vendorsStore = useVendorsStore();

const vendors = computed(() => vendorsStore.dropdownList);
const items = ref([]);
const warehouses = computed(() => resourcesStore.warehouses);

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  due_date: '',
  contact_id: '',
  warehouse: '',
  payment_terms: '',
  notes: '',
  items: []
});

const totals = computed(() => {
  const subtotal = form.items.reduce((s, i) => s + (i.qty || 0) * (i.unit_price || 0), 0);
  const discount = form.items.reduce((s, i) => {
    const sub = (i.qty || 0) * (i.unit_price || 0);
    return s + sub * ((i.discount || 0) / 100);
  }, 0);
  const tax = form.items.reduce((s, i) => {
    const sub = (i.qty || 0) * (i.unit_price || 0);
    const afterDisc = sub - sub * ((i.discount || 0) / 100);
    const rate = (i.taxes || []).reduce((r, tx) => { const m = tx.match(/(\d+(?:\.\d+)?)/); return r + (m ? parseFloat(m[1]) : 0); }, 0);
    return s + afterDisc * rate / 100;
  }, 0);
  return { subtotal, discount, tax, total: subtotal - discount + tax };
});

const formatNum = (n) => Number(n || 0).toFixed(2);
const errors = reactive({});
const itemErrors = ref([]);

const purchaseSchema = yup.object({
  date: yup.string().required('Invoice date is required.'),
  due_date: yup.string().required('Due date is required.'),
  contact_id: yup.mixed().required('Please select a vendor.').notOneOf([''], 'Please select a vendor.'),
  items: yup.array()
    .min(1, 'At least one item is required.')
    .of(yup.object({
      item_id: yup.mixed().required('Product is required.').notOneOf([''], 'Product is required.'),
      qty: yup.number().typeError('Qty must be a number.').min(1, 'Qty must be ≥ 1.').required('Qty is required.'),
      unit_price: yup.number().typeError('Valid price required.').min(0, 'Price must be ≥ 0.').required('Unit price is required.'),
    })),
});

const validate = async () => {
  Object.keys(errors).forEach(k => delete errors[k]);
  itemErrors.value = form.items.map(() => ({}));
  try {
    await purchaseSchema.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner.forEach(e => {
      const itemMatch = e.path.match(/^items\[(\d+)\]\.(.+)$/);
      if (itemMatch) {
        const i = Number(itemMatch[1]);
        if (!itemErrors.value[i]) itemErrors.value[i] = {};
        itemErrors.value[i][itemMatch[2]] = e.message;
      } else if (e.path === 'items') {
        errors.items = e.message;
      } else {
        errors[e.path] = e.message;
      }
    });
    return false;
  }
};

watch(() => form.date, () => { delete errors.date; });
watch(() => form.due_date, () => { delete errors.due_date; });
watch(() => form.contact_id, () => { delete errors.contact_id; });
watch(() => form.items.length, () => { if (form.items.length) delete errors.items; });

const addItem = () => form.items.push({ item_id: '', item_name: '', qty: 1, unit_price: 0, discount: 0, taxes: ['GST (18.00%)'], total: 0 });
const removeItem = (index) => form.items.splice(index, 1);

const selectItem = (index, event) => {
  const selected = items.value.find(i => i.id == event.target.value);
  if (selected) { form.items[index].item_name = selected.name; form.items[index].unit_price = selected.price || 0; calculateItemTotal(index); }
};

const calculateItemTotal = (index) => {
  const item = form.items[index];
  const sub = (item.qty || 0) * (item.unit_price || 0);
  const afterDisc = sub - sub * ((item.discount || 0) / 100);
  const rate = (item.taxes || []).reduce((r, tx) => { const m = tx.match(/(\d+(?:\.\d+)?)/); return r + (m ? parseFloat(m[1]) : 0); }, 0);
  item.total = afterDisc + afterDisc * rate / 100;
};

const savePurchase = async () => {
  if (!await validate()) return;
  try {
    await execute(() => resourcesStore.createPurchase({ ...form, subtotal: totals.value.subtotal, tax: totals.value.tax, total: totals.value.total }));
    notify.showSuccess(t('purchases.create_title'), t('purchases.created_success'));
    router.push('/purchases');
  } catch {
    notify.showError(t('common.save_failed'), t('purchases.create_failed'));
  }
};

onMounted(async () => {
  const due = new Date(); due.setDate(due.getDate() + 30);
  form.due_date = due.toISOString().split('T')[0];
  await Promise.allSettled([
    vendorsStore.fetchDropdown(),
    resourcesStore.fetchWarehouses(),
  ]);
  try {
    const ir = await api.get('/items');
    const payload = ir.data;
    items.value = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : []);
  } catch (e) { console.error('Items load error:', e); }
  addItem();
});
</script>
