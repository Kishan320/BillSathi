<template>
  <Teleport to="body">
    <div class="snackbar-container">
      <TransitionGroup name="snackbar">
        <div
          v-for="snack in snacks"
          :key="snack.id"
          class="snackbar"
          :class="snack.type"
        >
          <span class="snackbar-icon">
            <svg v-if="snack.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else-if="snack.type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <svg v-else-if="snack.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
          <div class="snackbar-body">
            <span class="snackbar-title">{{ snack.title }}</span>
            <span v-if="snack.description" class="snackbar-desc">{{ snack.description }}</span>
          </div>
          <button class="snackbar-close" @click="remove(snack.id)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';

const store = useNotificationsStore();
const snacks = ref([]);

watch(
  () => store.notifications[0],
  (notification) => {
    if (!notification) return;
    const snack = { ...notification };
    snacks.value.push(snack);
    setTimeout(() => remove(snack.id), 4000);
  }
);

const remove = (id) => {
  snacks.value = snacks.value.filter(s => s.id !== id);
};
</script>

<style scoped>
.snackbar-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}

.snackbar {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  min-width: 300px;
  max-width: 420px;
  padding: 14px 16px;
  border-radius: 10px;
  background: #1e1e2e;
  color: #e2e8f0;
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  pointer-events: all;
  border-left: 4px solid transparent;
}

.snackbar.success {
  background: var(--positive-bg);
  color: var(--positive);
  border-color: var(--positive);
}
.snackbar.error   { border-color: #ef4444; }
.snackbar.warning { border-color: #f59e0b; }
.snackbar.info    { border-color: #3b82f6; }

.snackbar-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  margin-top: 1px;
}
.snackbar-icon svg { width: 20px; height: 20px; }

.snackbar.success .snackbar-icon { color: var(--positive); }
.snackbar.error   .snackbar-icon { color: #ef4444; }
.snackbar.warning .snackbar-icon { color: #f59e0b; }
.snackbar.info    .snackbar-icon { color: #3b82f6; }

.snackbar-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.snackbar-title {
  font-size: 14px;
  font-weight: 600;
  line-height: 1.4;
}

.snackbar-desc {
  font-size: 12px;
  color: #94a3b8;
  line-height: 1.4;
}

.snackbar.success .snackbar-desc {
  color: #047857;
}

.snackbar-close {
  flex-shrink: 0;
  background: none;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 0;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.snackbar-close:hover { color: #e2e8f0; }
.snackbar.success .snackbar-close { color: #059669; }
.snackbar.success .snackbar-close:hover { color: #064e3b; }
.snackbar-close svg { width: 14px; height: 14px; }

.snackbar-enter-active,
.snackbar-leave-active {
  transition: all 0.3s ease;
}
.snackbar-enter-from {
  opacity: 0;
  transform: translateX(60px);
}
.snackbar-leave-to {
  opacity: 0;
  transform: translateX(60px);
}
</style>
