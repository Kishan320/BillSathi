<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="w-full max-w-2xl bg-card border border-border rounded-2xl shadow-xl overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-border">
        <h2 class="text-base font-semibold text-foreground">{{ isEdit ? 'Edit Bank Account' : 'New Bank Account' }}</h2>
        <button @click="emit('close')" class="text-muted-foreground hover:text-foreground transition-colors">✕</button>
      </div>

      <div class="p-5 space-y-4 max-h-[75vh] overflow-auto">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-muted-foreground mb-1">Account Type</label>
            <select v-model="form.account_type"
              :class="inputClass('account_type')"
            >
              <option value="cash">Cash</option>
              <option value="bank">Bank</option>
              <option value="credit_card">Credit Card</option>
              <option value="wallet">Wallet</option>
            </select>
            <p v-if="err('account_type')" class="text-xs text-danger mt-1">{{ err('account_type') }}</p>
          </div>

          <div>
            <label class="block text-xs text-muted-foreground mb-1">Currency</label>
            <select v-model="form.currency" :class="inputClass('currency')">
              <option value="INR">INR</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
            </select>
            <p v-if="err('currency')" class="text-xs text-danger mt-1">{{ err('currency') }}</p>
          </div>
        </div>

        <div>
          <label class="block text-xs text-muted-foreground mb-1">{{ nameLabel }}</label>
          <input v-model="form.name" type="text" :placeholder="namePlaceholder" :class="inputClass('name')" />
          <p v-if="err('name')" class="text-xs text-danger mt-1">{{ err('name') }}</p>
        </div>

        <!-- Cash -->
        <div v-if="form.account_type === 'cash'" class="grid grid-cols-2 gap-3">
          <div class="col-span-2">
            <label class="block text-xs text-muted-foreground mb-1">Opening Balance</label>
            <input v-model.number="form.opening_balance" type="number" step="0.01" :class="inputClass('opening_balance')" />
            <p v-if="err('opening_balance')" class="text-xs text-danger mt-1">{{ err('opening_balance') }}</p>
          </div>
        </div>

        <!-- Bank -->
        <div v-else-if="form.account_type === 'bank'" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Bank Name</label>
              <input v-model="form.bank_name" type="text" :class="inputClass('bank_name')" />
              <p v-if="err('bank_name')" class="text-xs text-danger mt-1">{{ err('bank_name') }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Account Holder Name</label>
              <input v-model="form.account_holder_name" type="text" :class="inputClass('account_holder_name')" />
              <p v-if="err('account_holder_name')" class="text-xs text-danger mt-1">{{ err('account_holder_name') }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Account Number</label>
              <input v-model="form.account_number" type="text" :class="inputClass('account_number')" />
              <p v-if="err('account_number')" class="text-xs text-danger mt-1">{{ err('account_number') }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">IFSC / SWIFT</label>
              <input v-model="form.ifsc_swift_code" type="text" :class="inputClass('ifsc_swift_code')" />
              <p v-if="err('ifsc_swift_code')" class="text-xs text-danger mt-1">{{ err('ifsc_swift_code') }}</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Branch Name</label>
              <input v-model="form.branch_name" type="text" :class="inputClass('branch_name')" />
              <p v-if="err('branch_name')" class="text-xs text-danger mt-1">{{ err('branch_name') }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Opening Balance</label>
              <input v-model.number="form.opening_balance" type="number" step="0.01" :class="inputClass('opening_balance')" />
              <p v-if="err('opening_balance')" class="text-xs text-danger mt-1">{{ err('opening_balance') }}</p>
            </div>
          </div>
        </div>

        <!-- Credit Card -->
        <div v-else-if="form.account_type === 'credit_card'" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Last 4 Digits</label>
              <input v-model="form.last_4_digits" type="text" maxlength="4" inputmode="numeric" :class="inputClass('last_4_digits')" />
              <p v-if="err('last_4_digits')" class="text-xs text-danger mt-1">{{ err('last_4_digits') }}</p>
            </div>
            <div>
              <label class="block text-xs text-muted-foreground mb-1">Billing Cycle Date (1-31)</label>
              <input v-model.number="form.billing_cycle_date" type="number" min="1" max="31" :class="inputClass('billing_cycle_date')" />
              <p v-if="err('billing_cycle_date')" class="text-xs text-danger mt-1">{{ err('billing_cycle_date') }}</p>
            </div>
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">Opening Balance</label>
            <input v-model.number="form.opening_balance" type="number" step="0.01" :class="inputClass('opening_balance')" />
            <p v-if="err('opening_balance')" class="text-xs text-danger mt-1">{{ err('opening_balance') }}</p>
          </div>
        </div>

        <!-- Wallet -->
        <div v-else class="space-y-3">
          <div>
            <label class="block text-xs text-muted-foreground mb-1">Wallet Provider</label>
            <input v-model="form.wallet_provider_name" type="text" :class="inputClass('wallet_provider_name')" />
            <p v-if="err('wallet_provider_name')" class="text-xs text-danger mt-1">{{ err('wallet_provider_name') }}</p>
          </div>
          <div>
            <label class="block text-xs text-muted-foreground mb-1">Opening Balance</label>
            <input v-model.number="form.opening_balance" type="number" step="0.01" :class="inputClass('opening_balance')" />
            <p v-if="err('opening_balance')" class="text-xs text-danger mt-1">{{ err('opening_balance') }}</p>
          </div>
        </div>
      </div>

      <div class="px-5 py-4 border-t border-border flex items-center justify-end gap-2">
        <button @click="emit('close')" class="px-5 py-2 text-sm font-medium border border-border rounded-full text-foreground hover:bg-muted transition-colors">
          Cancel
        </button>
        <button @click="onSubmit" :disabled="saving"
          class="px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-full hover:bg-primary/90 disabled:opacity-50 transition-colors">
          {{ saving ? 'SAVING...' : (isEdit ? 'UPDATE' : 'CREATE') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, watch } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
  account: { type: Object, default: null },
  errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'submit']);

const emptyForm = () => ({
  account_type: 'bank',
  currency: 'INR',
  name: '',
  opening_balance: 0,
  bank_name: '',
  account_holder_name: '',
  account_number: '',
  ifsc_swift_code: '',
  branch_name: '',
  last_4_digits: '',
  billing_cycle_date: null,
  wallet_provider_name: '',
});

const form = reactive(emptyForm());

const isEdit = computed(() => !!props.account?.id);

const nameLabel = computed(() => {
  if (form.account_type === 'credit_card') return 'Card Name';
  if (form.account_type === 'wallet') return 'Wallet Name';
  if (form.account_type === 'cash') return 'Cash Account Name';
  return 'Account Name';
});

const namePlaceholder = computed(() => {
  if (form.account_type === 'credit_card') return 'e.g. HDFC Credit Card';
  if (form.account_type === 'wallet') return 'e.g. Paytm Wallet';
  if (form.account_type === 'cash') return 'e.g. Cash';
  return 'e.g. Business Account';
});

const err = (k) => {
  const e = props.errors?.[k];
  return Array.isArray(e) ? e[0] : e || '';
};

const inputBase =
  'w-full px-3 py-2 bg-card border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors';

const inputClass = (k) => [inputBase, err(k) ? 'border-danger' : 'border-border'];

const loadFromAccount = (account) => {
  const meta = account?.metadata || {};
  Object.assign(form, emptyForm());
  if (!account) return;

  form.account_type = account.account_type || 'bank';
  form.currency = account.currency || 'INR';
  form.name = account.name || '';
  form.opening_balance = account.opening_balance ?? 0;

  form.bank_name = meta.bank_name || '';
  form.account_holder_name = meta.account_holder_name || '';
  form.account_number = meta.account_number || '';
  form.ifsc_swift_code = meta.ifsc_swift_code || '';
  form.branch_name = meta.branch_name || '';

  form.last_4_digits = meta.last_4_digits || '';
  form.billing_cycle_date = meta.billing_cycle_date ?? null;

  form.wallet_provider_name = meta.wallet_provider_name || '';
};

watch(
  () => [props.open, props.account],
  ([open]) => {
    if (open) loadFromAccount(props.account);
  },
  { immediate: true }
);

const frontendValidate = () => {
  const errs = {};
  const req = (k, msg) => {
    if (!form[k] && form[k] !== 0) errs[k] = msg;
  };

  req('account_type', 'Account type is required');
  req('currency', 'Currency is required');
  req('name', 'Name is required');

  if (form.account_type === 'bank') {
    req('bank_name', 'Bank name is required');
    req('account_holder_name', 'Account holder name is required');
    req('account_number', 'Account number is required');
  }

  if (form.account_type === 'credit_card') {
    req('last_4_digits', 'Last 4 digits is required');
    if (form.last_4_digits && !/^[0-9]{4}$/.test(String(form.last_4_digits))) errs.last_4_digits = 'Enter exactly 4 digits';
    req('billing_cycle_date', 'Billing cycle date is required');
    if (form.billing_cycle_date && (form.billing_cycle_date < 1 || form.billing_cycle_date > 31)) errs.billing_cycle_date = 'Must be between 1 and 31';
  }

  if (form.account_type === 'wallet') {
    req('wallet_provider_name', 'Wallet provider is required');
  }

  return errs;
};

const onSubmit = () => {
  const errs = frontendValidate();
  if (Object.keys(errs).length) {
    emit('submit', { ok: false, errors: errs });
    return;
  }

  emit('submit', {
    ok: true,
    payload: {
      account_type: form.account_type,
      currency: form.currency,
      name: form.name,
      opening_balance: form.opening_balance ?? 0,
      bank_name: form.bank_name || null,
      account_holder_name: form.account_holder_name || null,
      account_number: form.account_number || null,
      ifsc_swift_code: form.ifsc_swift_code || null,
      branch_name: form.branch_name || null,
      last_4_digits: form.last_4_digits || null,
      billing_cycle_date: form.billing_cycle_date ?? null,
      wallet_provider_name: form.wallet_provider_name || null,
    },
  });
};
</script>

