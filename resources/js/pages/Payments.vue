<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">Payments</h1>
        <p class="text-sm text-muted-foreground">Pay or receive from contacts</p>
      </div>
      <button @click="openModal()" class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
        New Payment
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4">
      <input v-model="search" @input="onSearch" type="text" placeholder="Search by reference..."
        class="flex-1 max-w-xs px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      <select v-model="filterType" @change="reload"
        class="px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
        <option value="">All</option>
        <option value="sent">Sent</option>
        <option value="received">Received</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-muted/50 border-b border-border">
          <tr>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Date</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Contact</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Type</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Amount</th>
            <th class="text-left px-4 py-3 text-muted-foreground font-medium">Reference</th>
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
            <td colspan="6" class="text-center py-10 text-muted-foreground">No payments found.</td>
          </tr>
          <tr v-else v-for="p in rows" :key="p.id" class="border-t border-border hover:bg-muted/30 transition-colors">
            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(p.date) }}</td>
            <td class="px-4 py-3 text-foreground font-medium">{{ p.contact?.name || '—' }}</td>
            <td class="px-4 py-3">
              <span :class="typeClass(p.type)" class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ (p.type || 'sent').toUpperCase() }}
              </span>
            </td>
            <td class="px-4 py-3 font-semibold text-foreground tabular-nums">{{ formatMoney(p.amount) }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ p.reference || '—' }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-2">
                <button @click="openModal(p)" type="button" title="Edit"
                  class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors hover:bg-primary hover:text-primary-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                  <AppIcon name="edit" :size="15" />
                </button>
                <button @click="deletePayment(p)" type="button" title="Delete"
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
      <div class="w-full max-w-4xl bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
          <h2 class="text-base font-semibold text-foreground">{{ editId ? 'Edit Payment' : 'New Payment' }}</h2>
          <button @click="closeModal" class="text-muted-foreground hover:text-foreground">✕</button>
        </div>

        <div class="p-5 space-y-5 max-h-[75vh] overflow-auto">
          <div class="grid grid-cols-2 gap-6">
            <!-- Left -->
            <div class="space-y-4">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">Date</label>
                <input v-model="form.date" type="date"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.date ? 'border-danger' : 'border-border']" />
                <p v-if="errors.date" class="text-xs text-danger mt-1">{{ errors.date }}</p>
              </div>

              <div>
                <label class="block text-xs text-muted-foreground mb-1">Contact</label>
                <select v-model="form.contact_id" @change="onContactChange"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.contact_id ? 'border-danger' : 'border-border']">
                  <option :value="null">—</option>
                  <option v-for="c in contactOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <p v-if="errors.contact_id" class="text-xs text-danger mt-1">{{ errors.contact_id }}</p>
              </div>

              <div>
                <label class="block text-xs text-muted-foreground mb-1">Bank Account</label>
                <select v-model="form.bank_account_id"
                  :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.bank_account_id ? 'border-danger' : 'border-border']">
                  <option :value="null">—</option>
                  <option v-for="a in bankAccountOptions" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
                <p class="text-[11px] text-muted-foreground mt-1" v-if="selectedBankAccount">Balance: {{ formatMoney(selectedBankAccount.current_balance, selectedBankAccount.currency) }}</p>
                <p v-if="errors.bank_account_id" class="text-xs text-danger mt-1">{{ errors.bank_account_id }}</p>
              </div>
            </div>

            <!-- Right -->
            <div class="space-y-4">
              <div>
                <label class="block text-xs text-muted-foreground mb-1">Pending Amount</label>
                <div class="flex items-center justify-between px-3 py-2 border border-border rounded-lg bg-muted/30">
                  <div class="text-sm font-semibold text-foreground tabular-nums">{{ formatMoney(pendingAmountValue, pendingCurrency) }}</div>
                  <span v-if="pendingBadge" :class="pendingBadge.class" class="px-2 py-0.5 rounded-full text-[11px] font-medium">
                    {{ pendingBadge.label }}
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs text-muted-foreground mb-1">Type</label>
                  <select v-model="form.type"
                    class="w-full px-3 py-2 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                    <option value="sent">Sent</option>
                    <option value="received">Received</option>
                  </select>
                  <p v-if="errors.type" class="text-xs text-danger mt-1">{{ errors.type }}</p>
                </div>
                <div>
                  <label class="block text-xs text-muted-foreground mb-1">Paid Amount</label>
                  <input v-model.number="form.amount" type="number" step="0.01" min="0"
                    :class="['w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors', errors.amount ? 'border-danger' : 'border-border']" />
                  <p v-if="errors.amount" class="text-xs text-danger mt-1">{{ errors.amount }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Transactions -->
          <div>
            <h3 class="text-sm font-semibold text-foreground mb-2">Transactions</h3>
            <div class="border border-border rounded-xl overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-muted/50 border-b border-border">
                  <tr>
                    <th class="px-3 py-2 w-10">
                      <input type="checkbox" v-model="txnSelected" class="h-4 w-4 rounded border-border bg-card text-primary focus:ring-primary/30" />
                    </th>
                    <th class="text-left px-3 py-2 text-muted-foreground font-medium">Description</th>
                    <th class="text-left px-3 py-2 text-muted-foreground font-medium">Due Date</th>
                    <th class="text-left px-3 py-2 text-muted-foreground font-medium">Pending Amount</th>
                    <th class="text-right px-3 py-2 text-muted-foreground font-medium">Pay / Receive</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-t border-border">
                    <td class="px-3 py-3">
                      <input type="checkbox" v-model="txnSelected" class="h-4 w-4 rounded border-border bg-card text-primary focus:ring-primary/30" />
                    </td>
                    <td class="px-3 py-3 text-foreground">Opening Balance</td>
                    <td class="px-3 py-3 text-muted-foreground">{{ form.date }}</td>
                    <td class="px-3 py-3 text-foreground tabular-nums">{{ formatMoney(pendingAmountValue, pendingCurrency) }}</td>
                    <td class="px-3 py-3 text-right tabular-nums">{{ formatMoney(form.amount || 0, pendingCurrency) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="inline-flex items-center gap-2 text-sm text-muted-foreground select-none">
                <input type="checkbox" v-model="sendNotification" class="h-4 w-4 rounded border-border bg-card text-primary focus:ring-primary/30" />
                Send notification to Contact
              </label>
              <label class="inline-flex items-center gap-2 text-sm text-muted-foreground select-none">
                <input type="checkbox" v-model="printPdf" class="h-4 w-4 rounded border-border bg-card text-primary focus:ring-primary/30" />
                Print PDF of Receipt
              </label>
            </div>

            <div class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">Total</span>
                <span class="text-foreground tabular-nums">{{ formatMoney(form.amount || 0, pendingCurrency) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">(+) Extra Receipt</span>
                <div class="flex items-center gap-2">
                  <input v-model.number="extraReceipt" type="number" step="0.01" min="0"
                    class="w-28 px-2 py-1.5 bg-card border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors" />
                </div>
              </div>
              <div class="border-t border-border pt-2 flex items-center justify-between text-sm font-semibold">
                <span class="text-muted-foreground">Net Amount</span>
                <span class="text-foreground tabular-nums">{{ formatMoney(netAmount, pendingCurrency) }}</span>
              </div>
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
          <button @click="savePayment" :disabled="saving"
            class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
            {{ saving ? 'SAVING...' : (editId ? 'UPDATE' : 'RECEIPT') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import * as yup from 'yup';
import { useNotificationsStore } from '@/stores/notifications';
import { usePaymentsStore } from '@/stores/payments';
import { useContactsStore } from '@/stores/contacts';
import { useBankAccountsStore } from '@/stores/bankAccounts';
import AppIcon from '@/components/ui/AppIcon.vue';

const notify = useNotificationsStore();
const paymentsStore = usePaymentsStore();
const contactsStore = useContactsStore();
const bankAccountsStore = useBankAccountsStore();

const saving = ref(false);
const showModal = ref(false);
const editId = ref(null);

const rows = computed(() => paymentsStore.list);
const meta = computed(() => paymentsStore.meta);
const loading = computed(() => paymentsStore.loading);

const search = ref('');
const filterType = ref('');
const perPage = ref(10);

let searchTimer = null;

const fetchDatatable = (page = 1) =>
  paymentsStore.fetchList({
    page,
    per_page: perPage.value,
    search: search.value,
    type: filterType.value,
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

const typeClass = (type) => {
  if (type === 'received') return 'bg-success/10 text-success';
  return 'bg-warning/10 text-warning';
};

const defaultForm = () => ({
  contact_id: null,
  bank_account_id: null,
  reference: '',
  date: new Date().toISOString().slice(0, 10),
  amount: 0,
  type: 'sent',
  method: '',
  notes: '',
});

const form = reactive(defaultForm());
const errors = reactive({});

const txnSelected = ref(true);
const sendNotification = ref(true);
const printPdf = ref(false);
const extraReceipt = ref(0);

const netAmount = computed(() => Number(form.amount || 0) + Number(extraReceipt.value || 0));

const contactOptions = computed(() => contactsStore.list);
const bankAccountOptions = computed(() => bankAccountsStore.list.filter((a) => a.status !== 'closed'));

const selectedContact = computed(() => contactOptions.value.find((c) => c.id === form.contact_id) || null);
const selectedBankAccount = computed(() => bankAccountOptions.value.find((a) => a.id === form.bank_account_id) || null);

const pendingCurrency = computed(() => selectedContact.value?.currency || 'INR');
const pendingAmountValue = computed(() => Number(selectedContact.value?.opening_balance || 0));
const pendingType = computed(() => selectedContact.value?.opening_balance_type || 'payable');
const pendingBadge = computed(() => {
  if (!selectedContact.value) return null;
  if (pendingType.value === 'receivable') return { label: 'Receivable', class: 'bg-success/10 text-success' };
  return { label: 'Payable', class: 'bg-warning/10 text-warning' };
});

const paymentSchema = computed(() => yup.object().shape({
  date: yup.string().required('Date is required.'),
  contact_id: yup.number().nullable().required('Contact is required.'),
  bank_account_id: yup.number().nullable().required('Bank account is required.'),
  type: yup.string().nullable().oneOf(['sent', 'received']).required('Type is required.'),
  amount: yup.number()
    .typeError('Amount must be a number.')
    .required('Amount is required.')
    .min(0, 'Amount must be 0 or greater.')
    .test('ltePending', 'Amount cannot exceed pending amount.', function (val) {
      const pending = pendingAmountValue.value || 0;
      if (!txnSelected.value) return true;
      return Number(val || 0) <= Number(pending || 0);
    }),
  reference: yup.string().nullable().max(100, 'Reference can be max 100 characters.'),
  method: yup.string().nullable().max(50, 'Method can be max 50 characters.'),
  notes: yup.string().nullable(),
}));

const validateForm = async () => {
  Object.keys(errors).forEach((k) => delete errors[k]);
  try {
    await paymentSchema.value.validate(form, { abortEarly: false });
    return true;
  } catch (err) {
    err.inner?.forEach((e) => { if (e.path) errors[e.path] = e.message; });
    return false;
  }
};

const onContactChange = () => {
  // Auto-select type based on pending direction
  if (pendingType.value === 'receivable') form.type = 'received';
  else form.type = 'sent';
  // Default amount to pending (optional)
  form.amount = Math.min(Number(form.amount || 0), pendingAmountValue.value || 0);
};

const openModal = async (payment = null) => {
  editId.value = payment?.id ?? null;
  Object.assign(form, defaultForm(), payment ?? {});
  Object.keys(errors).forEach((k) => delete errors[k]);
  showModal.value = true;

  txnSelected.value = true;
  sendNotification.value = true;
  printPdf.value = false;
  extraReceipt.value = 0;

  contactsStore.fetchList({ page: 1, per_page: 50, search: '' });
  bankAccountsStore.setIncludeClosed(false);
  bankAccountsStore.fetchAccounts({ force: true });
};

const closeModal = () => { showModal.value = false; };

const savePayment = async () => {
  if (!await validateForm()) return;
  saving.value = true;
  try {
    const payload = {
      contact_id: form.contact_id || null,
      bank_account_id: form.bank_account_id || null,
      reference: form.reference || null,
      date: form.date,
      amount: form.amount,
      type: form.type,
      method: form.method || null,
      notes: form.notes || null,
    };

    if (editId.value) {
      await paymentsStore.updatePayment(editId.value, payload);
      notify.showSuccess('Updated', 'Payment updated successfully');
    } else {
      await paymentsStore.createPayment(payload);
      notify.showSuccess('Created', 'Payment created successfully');
    }

    // Refresh contacts so pending amounts update
    contactsStore.fetchList({ page: 1, per_page: 50, search: '' });

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

const deletePayment = async (payment) => {
  if (!confirm(`Delete payment "${payment.reference || payment.id}"?`)) return;
  try {
    await paymentsStore.deletePayment(payment.id);
    notify.showSuccess('Deleted', 'Payment deleted successfully');
    contactsStore.fetchList({ page: 1, per_page: 50, search: '' });
  } catch {
    notify.showError('Delete failed', 'Could not delete payment');
  }
};

const formatDate = (d) => {
  if (!d) return '—';
  try { return new Date(d).toLocaleDateString(); } catch { return d; }
};

const formatMoney = (amount, currency = 'INR') => {
  const n = Number(amount ?? 0);
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency || 'INR', maximumFractionDigits: 2 }).format(n);
  } catch {
    return n.toFixed(2);
  }
};

onMounted(reload);
</script>
