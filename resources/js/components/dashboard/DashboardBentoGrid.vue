<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Total Revenue -->
    <MetricCard
      title="Total Revenue"
      :value="formatCurrency(stats.totalRevenue)"
      :change="stats.revenueChange"
      description="vs last period"
      icon="trending-up"
      variant="success"
      :show-sparkline="true"
      :sparkline-data="stats.revenueSparkline"
    />

    <!-- Active Franchises -->
    <MetricCard
      title="Active Franchises"
      :value="stats.activeFranchises"
      :change="stats.franchisesChange"
      description="operational locations"
      icon="building-2"
      variant="info"
    />

    <!-- New Customers -->
    <MetricCard
      title="New Customers"
      :value="stats.newCustomers"
      :change="stats.customersChange"
      description="this month"
      icon="users"
      variant="default"
    />

    <!-- Compliance Score -->
    <MetricCard
      title="Compliance Score"
      :value="`${stats.complianceScore}%`"
      :change="stats.complianceChange"
      description="network average"
      icon="shield-check"
      :variant="complianceVariant"
      :trend="complianceTrend"
    />
  </div>

  <!-- Additional Stats Row -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Performance Overview -->
    <div class="lg:col-span-2 bg-card border border-border rounded-xl p-6">
      <h3 class="text-lg font-semibold text-foreground mb-4">Performance Overview</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold text-positive">{{ stats.performanceMetrics.growth }}%</div>
          <div class="text-xs text-muted-foreground">Growth Rate</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-primary">{{ stats.performanceMetrics.retention }}%</div>
          <div class="text-xs text-muted-foreground">Retention</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-warning">{{ stats.performanceMetrics.satisfaction }}/5</div>
          <div class="text-xs text-muted-foreground">Satisfaction</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-bold text-info">{{ stats.performanceMetrics.efficiency }}%</div>
          <div class="text-xs text-muted-foreground">Efficiency</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-card border border-border rounded-xl p-6">
      <h3 class="text-lg font-semibold text-foreground mb-4">Quick Actions</h3>
      <div class="space-y-3">
        <button
          v-for="action in quickActions"
          :key="action.id"
          @click="handleQuickAction(action)"
          class="w-full flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-muted transition-colors text-left"
        >
          <AppIcon :name="action.icon" :size="16" class="text-muted-foreground" />
          <div>
            <div class="text-sm font-medium text-foreground">{{ action.title }}</div>
            <div class="text-xs text-muted-foreground">{{ action.description }}</div>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import MetricCard from '@/components/ui/MetricCard.vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const stats = ref({
  totalRevenue: 2458760,
  revenueChange: 12.5,
  revenueSparkline: [65, 78, 90, 81, 95, 88, 92, 100],
  activeFranchises: 156,
  franchisesChange: 8.2,
  newCustomers: 1248,
  customersChange: -3.1,
  complianceScore: 94,
  complianceChange: 2.1,
  performanceMetrics: {
    growth: 18.5,
    retention: 92,
    satisfaction: 4.6,
    efficiency: 87
  }
});

const quickActions = [
  {
    id: 'add-franchise',
    title: 'Add Franchise',
    description: 'Register new location',
    icon: 'plus',
    route: '/franchise-management?action=add'
  },
  {
    id: 'run-report',
    title: 'Run Report',
    description: 'Generate analytics',
    icon: 'bar-chart-3',
    route: '/reports'
  },
  {
    id: 'check-compliance',
    title: 'Check Compliance',
    description: 'Review status',
    icon: 'shield-check',
    route: '/compliance'
  }
];

const complianceVariant = computed(() => {
  const score = stats.value.complianceScore;
  if (score >= 90) return 'success';
  if (score >= 80) return 'warning';
  return 'danger';
});

const complianceTrend = computed(() => {
  const change = stats.value.complianceChange;
  if (change > 0) return 'up';
  if (change < 0) return 'down';
  return 'neutral';
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
};

const handleQuickAction = (action) => {
  router.push(action.route);
};

onMounted(() => {
  // Fetch dashboard stats from API
  console.log('Dashboard mounted - fetching stats');
});
</script>
