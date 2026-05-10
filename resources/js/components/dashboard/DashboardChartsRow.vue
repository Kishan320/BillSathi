<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Revenue Chart -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-foreground">Revenue Trend</h3>
        <select
          v-model="revenuePeriod"
          class="text-sm bg-muted border border-border rounded-lg px-2 py-1 text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
        >
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
        </select>
      </div>
      
      <div class="h-64 flex items-center justify-center text-muted-foreground">
        <div class="text-center">
          <AppIcon name="bar-chart-3" :size="48" class="mx-auto mb-2 opacity-50" />
          <p class="text-sm">Revenue chart will be displayed here</p>
          <p class="text-xs">Integrate with your preferred charting library</p>
        </div>
      </div>
    </div>

    <!-- Franchise Performance -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-foreground">Franchise Performance</h3>
        <button
          @click="refreshPerformance"
          class="text-sm text-primary hover:underline"
        >
          Refresh
        </button>
      </div>
      
      <div class="space-y-4">
        <div
          v-for="franchise in topFranchises"
          :key="franchise.id"
          class="flex items-center justify-between p-3 rounded-lg bg-muted/50"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
              <span class="text-xs font-semibold text-primary">
                {{ franchise.name.charAt(0) }}
              </span>
            </div>
            <div>
              <div class="text-sm font-medium text-foreground">{{ franchise.name }}</div>
              <div class="text-xs text-muted-foreground">{{ franchise.location }}</div>
            </div>
          </div>
          
          <div class="text-right">
            <div class="text-sm font-semibold text-foreground">
              {{ formatCurrency(franchise.revenue) }}
            </div>
            <div
              :class="[
                'text-xs font-medium',
                franchise.growth >= 0 ? 'text-positive' : 'text-danger'
              ]"
            >
              {{ franchise.growth >= 0 ? '+' : '' }}{{ franchise.growth }}%
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const revenuePeriod = ref('monthly');

const topFranchises = ref([
  {
    id: 1,
    name: 'Miami Beach',
    location: 'Florida',
    revenue: 245600,
    growth: 12.5
  },
  {
    id: 2,
    name: 'NYC Midtown',
    location: 'New York',
    revenue: 189300,
    growth: 8.2
  },
  {
    id: 3,
    name: 'Austin TX',
    location: 'Texas',
    revenue: 156800,
    growth: -2.1
  },
  {
    id: 4,
    name: 'Denver CO',
    location: 'Colorado',
    revenue: 134200,
    growth: 15.7
  }
]);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
};

const refreshPerformance = () => {
  // Simulate data refresh
  console.log('Refreshing franchise performance data');
};

onMounted(() => {
  // Fetch chart data and franchise performance
  console.log('DashboardChartsRow mounted');
});
</script>
