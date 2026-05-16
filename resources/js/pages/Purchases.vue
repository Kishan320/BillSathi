<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">{{ $t('purchases.title') }}</h1>
        <p class="text-sm text-muted-foreground">{{ $t('purchases.subtitle') }}</p>
      </div>
      <button @click="router.push('/purchases/create')" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        {{ $t('purchases.new') }}
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" :placeholder="$t('purchases.search_placeholder')"
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterStatus" @change="reload"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">{{ $t('common.all_status') }}</option>
        <option value="draft">{{ $t('purchases.status_draft') }}</option>
        <option value="posted">{{ $t('purchases.status_posted') }}</option>
        <option value="paid">{{ $t('purchases.status_paid') }}</option>
        <option value="partial">{{ $t('purchases.status_partial') }}</option>
        <option value="overdue">{{ $t('purchases.status_overdue') }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th @click="setSort('invoice_number')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_invoice') }} <span class="ml-1">{{ sortIcons['invoice_number'] }}</span>
            </th>
            <th @click="setSort('contact')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_vendor') }} <span class="ml-1">{{ sortIcons['contact'] }}</span>
            </th>
            <th @click="setSort('date')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_date') }} <span class="ml-1">{{ sortIcons['date'] }}</span>
            </th>
            <th @click="setSort('due_date')" class="text-left px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_due_date') }} <span class="ml-1">{{ sortIcons['due_date'] }}</span>
            </th>
            <th @click="setSort('subtotal')" class="text-right px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_subtotal') }} <span class="ml-1">{{ sortIcons['subtotal'] }}</span>
            </th>
            <th @click="setSort('tax')" class="text-right px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_tax') }} <span class="ml-1">{{ sortIcons['tax'] }}</span>
            </th>
            <th @click="setSort('total')" class="text-right px-4 py-3 text-muted-foreground font-medium cursor-pointer select-none">
              {{ $t('purchases.col_total') }} <span class="ml-1">{{ sortIcons['total'] }}</span>
            </th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">{{ $t('purchases.col_status') }}</th>
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
          <tr v-else-if="!rows.length"><td colspan="9" class="text-center py-10 text-muted-foreground">{{ $t('common.no_data', { resource: $t('purchases.resource') }) }}</td></tr>
          <tr v-else v-for="purchase in rows" :key="purchase.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 font-medium text-primary cursor-pointer hover:underline" @click="router.push(`/purchases/${purchase.id}`)">
              {{ purchase.invoice_number || '—' }}
            </td>
            <td class="px-4 py-3 text-foreground">{{ purchase.contact?.billing_name || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(purchase.date) }}</td>
            <td class="px-4 py-3">
              <span :class="isOverdue(purchase.due_date) ? 'text-danger' : 'text-muted-foreground'">
                {{ formatDate(purchase.due_date) }}
              </span>
            </td>
            <td class="px-4 py-3 text-right text-muted-foreground">₹{{ formatNum(purchase.subtotal) }}</td>
            <td class="px-4 py-3 text-right text-muted-foreground">₹{{ formatNum(purchase.tax) }}</td>
            <td class="px-4 py-3 text-right font-medium">₹{{ formatNum(purchase.total) }}</td>
            <td class="px-4 py-3">
              <span :class="statusClass(purchase.status)" class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ purchase.status }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="router.push(`/purchases/${purchase.id}/edit`)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deletePurchase(purchase)" type="button" title="Delete"
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
          {{ $t('common.showing', { from: meta.from, to: meta.to, total: meta.total, resource: $t('purchases.resource') }) }}
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
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';
import AppIcon from '@/components/ui/AppIcon.vue';

const router = useRouter();
const notify = useNotificationsStore();
const resourcesStore = useResourcesStore();

const loading = ref(false);
const rows = computed(() => resourcesStore.purchasesList);
const meta = computed(() => resourcesStore.purchasesMeta);
const search = ref('');
const filterStatus = ref('');
const perPage = ref(10);
const sortCol = ref('date');
const sortDir = ref('desc');

let searchTimer = null;

const COLUMNS = ['invoice_number', 'contact', 'date', 'due_date', 'subtotal', 'tax', 'total', 'status'];

const fetchDatatable = async (page = 1) => {
  loading.value = true;
  try {
    const colIndex = COLUMNS.indexOf(sortCol.value);
    await resourcesStore.fetchPurchases({
      draw: 1,
      start: (page - 1) * perPage.value,
      length: perPage.value,
      'search[value]': search.value,
      'order[0][column]': colIndex >= 0 ? colIndex : 0,
      'order[0][dir]': sortDir.value,
      status: filterStatus.value,
      page,
      ...COLUMNS.reduce((acc, col, i) => {
        acc[`columns[${i}][data]`] = col;
        acc[`columns[${i}][searchable]`] = 'true';
        acc[`columns[${i}][orderable]`] = 'true';
        return acc;
      }, {}),
    });
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
  if (sortCol.value === col) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  else { sortCol.value = col; sortDir.value = 'asc'; }
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

const formatDate = (date) => date ? new Date(date).toLocaleDateString() : '—';
const formatNum = (n) => Number(n || 0).toFixed(2);
const isOverdue = (dueDate) => dueDate && new Date(dueDate) < new Date();

const statusClass = (status) => {
  const map = { paid: 'status-active', posted: 'status-onboarding', draft: 'bg-muted text-muted-foreground', partial: 'status-onboarding', overdue: 'bg-danger/10 text-danger' };
  return map[status] || 'bg-muted text-muted-foreground';
};

const deletePurchase = async (purchase) => {
  if (!confirm(`Delete purchase "${purchase.invoice_number}"?`)) return;
  try {
    await resourcesStore.deletePurchase(purchase.id);
    notify.showSuccess('Deleted', `"${purchase.invoice_number}" has been deleted.`);
  } catch {
    notify.showError('Delete Failed', 'Could not delete the purchase.');
  }
};

onMounted(reload);
</script>
