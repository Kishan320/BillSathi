<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Network Overview</h1>
        <p class="text-sm text-muted-foreground mt-0.5">Hisaab — Global Command Center</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs text-muted-foreground bg-card border border-border rounded-lg px-3 py-2">
          <span class="w-1.5 h-1.5 rounded-full bg-positive animate-pulse" />
          Live data · Updated {{ lastUpdate }} ago
        </div>
        <select
          v-model="selectedPeriod"
          class="text-sm font-semibold bg-card border border-border rounded-lg px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
        >
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
          <option value="quarter">This quarter</option>
          <option value="year">This year</option>
        </select>
      </div>
    </div>

    <!-- Dashboard Content -->
    <DashboardBentoGrid />
    <DashboardChartsRow />
    <DashboardBottomRow />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import DashboardBentoGrid from '@/components/dashboard/DashboardBentoGrid.vue';
import DashboardChartsRow from '@/components/dashboard/DashboardChartsRow.vue';
import DashboardBottomRow from '@/components/dashboard/DashboardBottomRow.vue';

const selectedPeriod = ref('30');
const lastUpdate = ref('12s');
let updateInterval = null;

onMounted(() => {
  // Simulate live data updates
  updateInterval = setInterval(() => {
    const seconds = Math.floor(Math.random() * 30) + 1;
    lastUpdate.value = `${seconds}s`;
  }, 5000);
});

onUnmounted(() => {
  if (updateInterval) {
    clearInterval(updateInterval);
  }
});
</script>
