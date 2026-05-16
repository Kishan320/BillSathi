<template>
  <div class="bg-card border border-border rounded-2xl p-4 hover:shadow-sm transition-shadow relative">
    <div class="flex items-start gap-3">
      <div :class="['h-10 w-10 rounded-xl flex items-center justify-center', iconBg]">
        <AppIcon :name="iconName" :size="18" :class="iconColor" />
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <p class="text-sm font-semibold text-foreground truncate">{{ account.name }}</p>
            <p class="text-xs text-muted-foreground capitalize">
              {{ typeLabel }}
              <span v-if="account.status === 'closed'" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-muted text-muted-foreground">Closed</span>
            </p>
          </div>

          <div class="relative bank-account-menu">
            <button @click="toggleMenu" class="h-8 w-8 inline-flex items-center justify-center rounded-lg hover:bg-muted transition-colors text-muted-foreground">
              <AppIcon name="more-vertical" :size="16" />
            </button>

            <div v-if="menuOpen" class="absolute right-0 mt-2 w-44 bg-card border border-border rounded-xl shadow-lg overflow-hidden z-10">
              <button @click="onEdit" class="w-full px-3 py-2 text-left text-sm hover:bg-muted flex items-center gap-2">
                <AppIcon name="edit" :size="14" />
                Edit
              </button>
              <button @click="onClose" :disabled="account.status === 'closed'" class="w-full px-3 py-2 text-left text-sm hover:bg-muted flex items-center gap-2 disabled:opacity-50">
                <AppIcon name="x-circle" :size="14" />
                Close Account
              </button>
              <button @click="onDelete" class="w-full px-3 py-2 text-left text-sm hover:bg-muted flex items-center gap-2 text-danger">
                <AppIcon name="trash-2" :size="14" />
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="mt-3 flex items-end justify-between">
          <div>
            <p class="text-xs text-muted-foreground">Current Balance</p>
            <p class="text-lg font-semibold text-foreground tabular-nums">
              {{ formatMoney(account.current_balance, account.currency) }}
            </p>
          </div>
          <p class="text-xs text-muted-foreground tabular-nums">{{ account.currency }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const props = defineProps({
  account: { type: Object, required: true },
});

const emit = defineEmits(['edit', 'close', 'delete']);

const menuOpen = ref(false);
const onDocClick = (e) => {
  if (!menuOpen.value) return;
  const el = e.target;
  if (el?.closest?.('.bank-account-menu')) return;
  menuOpen.value = false;
  document.removeEventListener('click', onDocClick);
};

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value;
  if (menuOpen.value) document.addEventListener('click', onDocClick);
  else document.removeEventListener('click', onDocClick);
};

onBeforeUnmount(() => document.removeEventListener('click', onDocClick));

const iconName = computed(() => {
  if (props.account.account_type === 'cash') return 'banknote';
  if (props.account.account_type === 'credit_card') return 'credit-card';
  if (props.account.account_type === 'wallet') return 'wallet';
  return 'landmark';
});

const iconBg = computed(() => {
  if (props.account.account_type === 'cash') return 'bg-warning/10';
  if (props.account.account_type === 'credit_card') return 'bg-primary/10';
  if (props.account.account_type === 'wallet') return 'bg-success/10';
  return 'bg-info/10';
});

const iconColor = computed(() => {
  if (props.account.account_type === 'cash') return 'text-warning';
  if (props.account.account_type === 'credit_card') return 'text-primary';
  if (props.account.account_type === 'wallet') return 'text-success';
  return 'text-info';
});

const typeLabel = computed(() => {
  if (props.account.account_type === 'cash') return 'Cash';
  if (props.account.account_type === 'credit_card') return 'Credit Card';
  if (props.account.account_type === 'wallet') return 'Wallet';
  return 'Bank';
});

const formatMoney = (amount, currency) => {
  const n = Number(amount ?? 0);
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency || 'INR', maximumFractionDigits: 2 }).format(n);
  } catch {
    return n.toFixed(2);
  }
};

const onEdit = () => {
  menuOpen.value = false;
  emit('edit', props.account);
};
const onClose = () => {
  menuOpen.value = false;
  emit('close', props.account);
};
const onDelete = () => {
  menuOpen.value = false;
  emit('delete', props.account);
};
</script>
