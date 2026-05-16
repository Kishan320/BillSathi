<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">Incomes</h1>
        <p class="text-sm text-muted-foreground">Track and manage incomes</p>
      </div>
      <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        New Income
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" placeholder="Search by reference..."
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterStatus" @change="reload"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">All Status</option>
        <option value="received">Received</option>
        <option value="pending">Pending</option>
        <option value="overdue">Overdue</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Date</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Reference</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Category</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Amount</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Status</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="text-center py-10">
              <div class="inline-flex items-center gap-2 text-muted-foreground">
                <AppIcon name="refresh-cw" :size="18" class="animate-spin text-primary" />
              </div>
            </td>
          </tr>
          <tr v-else-if="!rows.length">
            <td colspan="6" class="text-center py-10 text-muted-foreground">No incomes found.</td>
          </tr>
          <tr v-else v-for="income in rows" :key="income.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(income.date) }}</td>
            <td class="px-4 py-3 font-medium text-foreground">{{ income.reference || '—' }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ income.category || '—' }}</td>
            <td class="px-4 py-3 font-semibold text-foreground tabular-nums">{{ formatMoney(income.amount) }}</td>
            <td class="px-4 py-3">
              <span :class="statusClass(income.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ (income.status || 'received').toUpperCase() }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(income)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deleteIncome(income)" type="button" title="Delete"
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
          Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }}
        </p>
        <div class="flex items-center gap-1">
          <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
            class="px-3 py-1.5 text-xs font-medium border border-border rounded-lg disabled:opacity-50 hover:bg-muted transition-colors">
            Prev
          </button>
          <button
            v-for="p in pageNumbers"
            :key="p"
            @click="p !== '...' && goToPage(p)"
            :class="[
              'px-3 py-1.5 text-xs font-medium border border-border rounded-lg hover:bg-muted transition-colors',
              p === meta.current_page ? 'bg-primary text-primary-foreground border-primary' : '',
              p === '...' ? 'cursor-default opacity-60' : ''
            ]"
          >
            {{ p }}
          </button>
          <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
            class="px-3 py-1.5 text-xs font-medium border border-border rounded-lg disabled:opacity-50 hover:bg-muted transition-colors">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="w-full max-w-2xl bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? 'Edit Income' : 'New Income' }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="p-5 space-y-4 max-h-[75vh] overflow-auto">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Date</label>
              <input v-model="form.date" type="date"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.date ? 'border-danger' : 'border-border']" />
              <p v-if="errors.date" class="text-xs text-danger mt-1">{{ errors.date }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Amount</label>
              <input v-model.number="form.amount" type="number" step="0.01" min="0"
                :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.amount ? 'border-danger' : 'border-border']" />
              <p v-if="errors.amount" class="text-xs text-danger mt-1">{{ errors.amount }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Status</label>
              <select v-model="form.status"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option value="received">Received</option>
                <option value="pending">Pending</option>
                <option value="overdue">Overdue</option>
              </select>
              <p v-if="errors.status" class="text-xs text-danger mt-1">{{ errors.status }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Category</label>
              <div class="flex gap-2">
                <select v-model="categorySelection" @change="onCategorySelect"
                  :class="['flex-1 px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.category ? 'border-danger' : 'border-border']">
                  <option value="">—</option>
                  <option v-for="c in categoryOptions" :key="c" :value="c">{{ c }}</option>
                  <option value="__add__">+ Add new category…</option>
                </select>
                <button
                  @click="categoriesModalOpen = true"
                  type="button"
                  class="px-3 py-2 text-xs font-medium border border-border rounded-lg hover:bg-muted transition-colors"
                  title="Manage categories"
                >
                  MANAGE
                </button>
              </div>
              <p v-if="errors.category" class="text-xs text-danger mt-1">{{ errors.category }}</p>
            </div>
          </div>

          <div v-if="addingCategory" class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
              <label class="block text-xs text-muted-foreground mb-1">New Category</label>
              <input v-model="newCategory" type="text" placeholder="Type and create"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
            </div>
            <div class="flex items-end">
              <button @click="createCategory" :disabled="categorySaving || !newCategory.trim()"
                class="w-full px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors">
                {{ categorySaving ? 'ADDING...' : 'ADD' }}
              </button>
            </div>
            <div class="col-span-3 -mt-2">
              <button @click="cancelAddCategory" type="button" class="text-xs text-muted-foreground hover:text-foreground">
                Cancel
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs text-muted-foreground mb-1">Reference</label>
            <input v-model="form.reference" type="text" placeholder="Optional"
              :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.reference ? 'border-danger' : 'border-border']" />
            <p v-if="errors.reference" class="text-xs text-danger mt-1">{{ errors.reference }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Contact (optional)</label>
              <select v-model="form.contact_id"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option :value="null">—</option>
                <option v-for="c in contactOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="errors.contact_id" class="text-xs text-danger mt-1">{{ errors.contact_id }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Bank Account (optional)</label>
              <select v-model="form.bank_account_id"
                class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                <option :value="null">—</option>
                <option v-for="a in bankAccountOptions" :key="a.id" :value="a.id">{{ a.name }}</option>
              </select>
              <p v-if="errors.bank_account_id" class="text-xs text-danger mt-1">{{ errors.bank_account_id }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs text-muted-foreground mb-1">Notes</label>
            <textarea v-model="form.notes" rows="3" placeholder="Optional"
              class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"></textarea>
            <p v-if="errors.notes" class="text-xs text-danger mt-1">{{ errors.notes }}</p>
          </div>
        </div>

        <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2">
          <button @click="closeModal" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
            Cancel
          </button>
          <button @click="saveIncome" :disabled="saving"
            class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? 'SAVING...' : (editId ? 'UPDATE' : 'CREATE') }}
          </button>
        </div>
      </div>
    </div>

    <ExpenseCategoryManager
      :open="categoriesModalOpen"
      @close="categoriesModalOpen = false"
      @updated="onCategoriesUpdated"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import * as yup from 'yup';
import { useNotificationsStore } from '@/stores/notifications';
import { useIncomesStore } from '@/stores/incomes';
import { useContactsStore } from '@/stores/contacts';
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { useResourcesStore } from '@/stores/resources';
import AppIcon from '@/components/ui/AppIcon.vue';
import ExpenseCategoryManager from '@/components/expenses/ExpenseCategoryManager.vue';

const notify = useNotificationsStore();
const incomesStore = useIncomesStore();
const contactsStore = useContactsStore();
const bankAccountsStore = useBankAccountsStore();
const resourcesStore = useResourcesStore();

const saving = ref(false);
const showModal = ref(false);
const editId = ref(null);

const rows = computed(() => incomesStore.list);
const meta = computed(() => incomesStore.meta);
const loading = computed(() => incomesStore.loading);

const search = ref('');
const filterStatus = ref('');
const perPage = ref(10);

let searchTimer = null;

const fetchDatatable = (page = 1) =>
  incomesStore.fetchList({
    page,
    per_page: perPage.value,
    search: search.value,
    status: filterStatus.value,
  });

const reload = () => fetchDatatable(1);

const onSearch = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(reload, 350);
};

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

const statusClass = (status) => {
  const s = status || 'received';
  if (s === 'received') return 'bg-success/10 text-success';
  if (s === 'pending') return 'bg-warning/10 text-warning';
  return 'bg-danger/10 text-danger';
};

const defaultForm = () => ({
  contact_id: null,
  bank_account_id: null,
  reference: '',
  date: new Date().toISOString().slice(0, 10),
  amount: 0,
  category: '',
  notes: '',
  status: 'received',
});

const form = reactive(defaultForm());
const errors = reactive({});

const categorySelection = ref('');
const addingCategory = ref(false);
const newCategory = ref('');
const categorySaving = ref(false);
const categoriesModalOpen = ref(false);

const categoryOptions = computed(() => {
  const cats = resourcesStore.setupOptions?.categories || [];
  return Array.from(new Set(cats.filter(Boolean))).sort((a, b) => a.localeCompare(b));
});

const incomeSchema = yup.object().shape({
  date: yup.string().required('Date is required.'),
  amount: yup.number().typeError('Amount must be a number.').required('Amount is required.').min(0, 'Amount must be 0 or greater.').max(999999999.99, 'Amount is too large.'),
  status: yup.string().nullable().oneOf(['received', 'pending', 'overdue']),
  reference: yup.string().nullable().max(100, 'Reference can be max 100 characters.'),
  category: yup.string().nullable().max(100, 'Category can be max 100 characters.'),
  notes: yup.string().nullable(),
  contact_id: yup.number().nullable(),
  bank_account_id: yup.number().nullable(),
});

const validateForm = async () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await incomeSchema.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner?.forEach((e) => { if (e.path) errors[e.path] = e.message; });
    return false;
  }
};

const contactOptions = computed(() => contactsStore.list);
const bankAccountOptions = computed(() => bankAccountsStore.list.filter((a) => a.status !== 'closed'));

const onCategorySelect = () => {
  if (categorySelection.value === '__add__') {
    addingCategory.value = true;
    newCategory.value = '';
    form.category = '';
    return;
  }
  addingCategory.value = false;
  newCategory.value = '';
  form.category = categorySelection.value || '';
};

const cancelAddCategory = () => {
  addingCategory.value = false;
  newCategory.value = '';
  categorySelection.value = form.category || '';
};

const createCategory = async () => {
  const value = newCategory.value.trim();
  if (!value) return;
  categorySaving.value = true;
  try {
    await resourcesStore.createSystemSetting({ type: 'categories', value });
    await resourcesStore.fetchSystemSettings({ force: true });
    form.category = value;
    categorySelection.value = value;
    addingCategory.value = false;
    newCategory.value = '';
    notify.showSuccess('Created', `"${value}" added to categories`);
  } catch (e) {
    notify.showError('Save Failed', e.response?.data?.message || 'Could not add category');
  } finally {
    categorySaving.value = false;
  }
};

const onCategoriesUpdated = async () => {
  await resourcesStore.fetchSystemSettings({ force: true });
  categorySelection.value = form.category || '';
};

const openModal = async (income = null) => {
  editId.value = income?.id ?? null;
  Object.assign(form, defaultForm(), income ?? {});
  Object.keys(errors).forEach((k) => delete errors[k]);
  showModal.value = true;
  categorySelection.value = form.category || '';
  addingCategory.value = false;
  newCategory.value = '';

  contactsStore.fetchList({ page: 1, per_page: 20, search: '' });
  bankAccountsStore.setIncludeClosed(false);
  bankAccountsStore.fetchAccounts({ force: true });
  resourcesStore.fetchSystemSettings();
};

const closeModal = () => { showModal.value = false; };

const saveIncome = async () => {
  if (!await validateForm()) return;
  saving.value = true;
  try {
    const payload = {
      contact_id: form.contact_id || null,
      bank_account_id: form.bank_account_id || null,
      reference: form.reference || null,
      date: form.date,
      amount: form.amount,
      category: form.category || null,
      notes: form.notes || null,
      status: form.status || 'received',
    };

    if (editId.value) {
      await incomesStore.updateIncome(editId.value, payload);
      notify.showSuccess('Updated', 'Income updated successfully');
    } else {
      await incomesStore.createIncome(payload);
      notify.showSuccess('Created', 'Income created successfully');
    }
    closeModal();
  } catch (e) {
    const backendErrors = e.response?.data?.errors;
    if (backendErrors) {
      Object.entries(backendErrors).forEach(([k, v]) => { errors[k] = v[0]; });
    } else {
      notify.showError('Save failed', e.response?.data?.message || 'Something went wrong');
    }
  } finally {
    saving.value = false;
  }
};

const deleteIncome = async (income) => {
  if (!confirm(`Delete income "${income.reference || income.id}"?`)) return;
  try {
    await incomesStore.deleteIncome(income.id);
    notify.showSuccess('Deleted', 'Income deleted successfully');
  } catch {
    notify.showError('Delete failed', 'Could not delete income');
  }
};

const formatDate = (d) => {
  if (!d) return '—';
  try { return new Date(d).toLocaleDateString(); } catch { return d; }
};

const formatMoney = (amount) => {
  const n = Number(amount ?? 0);
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'INR', maximumFractionDigits: 2 }).format(n);
  } catch {
    return n.toFixed(2);
  }
};

onMounted(reload);
</script>
