<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-foreground">Bank Accounts</h1>
        <p class="text-sm text-muted-foreground">Manage cash, bank, cards and wallets</p>
      </div>
      <div class="flex items-center gap-2">
        <label class="inline-flex items-center gap-2 text-sm text-muted-foreground select-none">
          <input type="checkbox" v-model="includeClosedLocal" @change="onToggleClosed"
            class="h-4 w-4 rounded border-border bg-card text-primary focus:ring-primary/30" />
          Show closed
        </label>
      </div>
    </div>

    <!-- Search -->
    <div class="flex gap-3 mb-5">
      <div class="flex-1 max-w-md relative">
        <AppIcon name="search" :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
        <input v-model="searchLocal" @input="onSearch" type="text" placeholder="Search accounts..."
          class="w-full pl-9 pr-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30" />
      </div>
    </div>

    <!-- Grid -->
    <div v-if="store.loading" class="py-14 text-center">
      <div class="inline-flex items-center gap-2 text-muted-foreground">
        <AppIcon name="refresh-cw" :size="18" class="animate-spin text-primary" />
        Loading...
      </div>
    </div>

    <div v-else-if="store.isEmpty" class="py-14 text-center text-muted-foreground">
      No bank accounts found.
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <BankAccountCard
        v-for="acc in store.list"
        :key="acc.id"
        :account="acc"
        @edit="store.openEdit"
        @close="onCloseAccount"
        @delete="onDeleteAccount"
      />
    </div>

    <!-- Floating Add Button -->
    <button @click="store.openCreate"
      class="fixed bottom-6 right-6 h-12 w-12 rounded-full bg-primary text-primary-foreground shadow-lg hover:bg-primary/90 transition-colors inline-flex items-center justify-center"
      aria-label="Add bank account"
    >
      <AppIcon name="plus" :size="20" />
    </button>

    <BankAccountModal
      :open="store.modalOpen"
      :saving="store.saving"
      :account="store.editing"
      :errors="mergedErrors"
      @close="store.closeModal"
      @submit="onSubmitModal"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import BankAccountCard from '@/components/bank-accounts/BankAccountCard.vue';
import BankAccountModal from '@/components/bank-accounts/BankAccountModal.vue';
import { useBankAccountsStore } from '@/stores/bankAccounts';
import { useNotificationsStore } from '@/stores/notifications';

const store = useBankAccountsStore();
const notifications = useNotificationsStore();

const searchLocal = ref(store.search);
const includeClosedLocal = ref(store.includeClosed);

const localErrors = ref({});
const mergedErrors = computed(() => ({ ...(store.fieldErrors || {}), ...(localErrors.value || {}) }));

onMounted(() => {
  store.fetchAccounts();
});

const onSearch = () => {
  store.setSearch(searchLocal.value);
};

const onToggleClosed = () => {
  store.setIncludeClosed(includeClosedLocal.value);
};

const onSubmitModal = async (e) => {
  if (!e.ok) {
    localErrors.value = e.errors || {};
    return;
  }

  localErrors.value = {};

  try {
    if (store.editing?.id) {
      await store.updateAccount(store.editing.id, e.payload);
      notifications.showSuccess('Updated', 'Bank account updated');
    } else {
      await store.createAccount(e.payload);
      notifications.showSuccess('Created', 'Bank account created');
    }
  } catch {
    // handled by store + global interceptor
  }
};

const onCloseAccount = async (account) => {
  if (account.status === 'closed') return;
  if (!confirm(`Close "${account.name}"? This will keep history but make it inactive.`)) return;
  try {
    await store.closeAccount(account);
    notifications.showSuccess('Closed', 'Account closed');
  } catch {
    //
  }
};

const onDeleteAccount = async (account) => {
  if (!confirm(`Delete "${account.name}"? This is only allowed when no transactions are linked.`)) return;
  try {
    await store.deleteAccount(account);
    notifications.showSuccess('Deleted', 'Account deleted');
  } catch {
    //
  }
};
</script>
