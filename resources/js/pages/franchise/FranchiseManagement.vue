<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Franchise Management</h1>
        <p class="text-sm text-muted-foreground mt-0.5">Manage your franchise network and locations</p>
      </div>
      <div class="flex items-center gap-3">
        <button
          @click="exportData"
          class="flex items-center gap-2 px-4 py-2 bg-card border border-border text-foreground font-medium rounded-lg hover:bg-muted transition-colors"
        >
          <AppIcon name="download" :size="16" />
          Export
        </button>
        <button
          @click="openAddModal"
          class="flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition-colors"
        >
          <AppIcon name="plus" :size="16" />
          Add Franchise
        </button>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">Search</label>
          <div class="relative">
            <AppIcon
              name="search"
              :size="16"
              class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search franchises..."
              class="w-full pl-10 pr-3 py-2 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 bg-muted border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="onboarding">Onboarding</option>
            <option value="terminated">Terminated</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">Region</label>
          <select
            v-model="filters.region"
            class="w-full px-3 py-2 bg-muted border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
          >
            <option value="">All Regions</option>
            <option value="north">North</option>
            <option value="south">South</option>
            <option value="east">East</option>
            <option value="west">West</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">Sort By</label>
          <select
            v-model="filters.sort"
            class="w-full px-3 py-2 bg-muted border border-border rounded-lg text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
          >
            <option value="name">Name</option>
            <option value="revenue">Revenue</option>
            <option value="created_at">Created Date</option>
            <option value="performance">Performance</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Franchises Table -->
    <div class="bg-card border border-border rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-muted/50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Franchise
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Location
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Manager
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Revenue
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Performance
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr
              v-for="franchise in filteredFranchises"
              :key="franchise.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="text-sm font-semibold text-primary">
                      {{ franchise.name.charAt(0) }}
                    </span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-foreground">
                      {{ franchise.name }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      ID: {{ franchise.id }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-foreground">{{ franchise.location }}</div>
                <div class="text-xs text-muted-foreground">{{ franchise.region }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-foreground">{{ franchise.manager }}</div>
                <div class="text-xs text-muted-foreground">{{ franchise.email }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-foreground">
                  {{ formatCurrency(franchise.revenue) }}
                </div>
                <div
                  :class="[
                    'text-xs font-medium',
                    franchise.revenueChange >= 0 ? 'text-positive' : 'text-danger'
                  ]"
                >
                  {{ franchise.revenueChange >= 0 ? '+' : '' }}{{ franchise.revenueChange }}%
                </div>
              </td>
              <td class="px-6 py-4">
                <StatusBadge :status="franchise.status" :text="franchise.status" />
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-muted rounded-full h-2">
                    <div
                      :class="[
                        'h-2 rounded-full',
                        getPerformanceColor(franchise.performance)
                      ]"
                      :style="{ width: `${franchise.performance}%` }"
                    />
                  </div>
                  <span class="text-xs font-medium text-foreground">
                    {{ franchise.performance }}%
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="viewFranchise(franchise)"
                    class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors"
                  >
                    <AppIcon name="eye" :size="14" />
                  </button>
                  <button
                    @click="editFranchise(franchise)"
                    class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-colors"
                  >
                    <AppIcon name="edit" :size="14" />
                  </button>
                  <button
                    @click="deleteFranchise(franchise)"
                    class="p-1.5 text-muted-foreground hover:text-danger hover:bg-danger/10 rounded-lg transition-colors"
                  >
                    <AppIcon name="trash-2" :size="14" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Showing {{ filteredFranchises.length }} of {{ franchises.length }} franchises
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <AppIcon name="chevron-left" :size="16" />
        </button>
        <span class="text-sm text-foreground px-3 py-1">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <AppIcon name="chevron-right" :size="16" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationsStore } from '@/stores/notifications';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const router = useRouter();
const notificationsStore = useNotificationsStore();

const franchises = ref([
  {
    id: 1,
    name: 'Miami Beach',
    location: '123 Ocean Drive, Miami Beach, FL',
    region: 'South',
    manager: 'Sarah Johnson',
    email: 'sarah.johnson@hisaab.com',
    revenue: 245600,
    revenueChange: 12.5,
    status: 'active',
    performance: 92
  },
  {
    id: 2,
    name: 'NYC Midtown',
    location: '456 Broadway, New York, NY',
    region: 'East',
    manager: 'Michael Chen',
    email: 'michael.chen@hisaab.com',
    revenue: 189300,
    revenueChange: 8.2,
    status: 'active',
    performance: 88
  },
  {
    id: 3,
    name: 'Austin TX',
    location: '789 Congress Ave, Austin, TX',
    region: 'South',
    manager: 'Robert Davis',
    email: 'robert.davis@hisaab.com',
    revenue: 156800,
    revenueChange: -2.1,
    status: 'review',
    performance: 75
  },
  {
    id: 4,
    name: 'Denver CO',
    location: '321 Market Street, Denver, CO',
    region: 'West',
    manager: 'Emily Wilson',
    email: 'emily.wilson@hisaab.com',
    revenue: 134200,
    revenueChange: 15.7,
    status: 'active',
    performance: 85
  },
  {
    id: 5,
    name: 'Seattle Downtown',
    location: '654 Pike Street, Seattle, WA',
    region: 'West',
    manager: 'David Martinez',
    email: 'david.martinez@hisaab.com',
    revenue: 98700,
    revenueChange: 5.3,
    status: 'onboarding',
    performance: 68
  }
]);

const filters = ref({
  search: '',
  status: '',
  region: '',
  sort: 'name'
});

const currentPage = ref(1);
const itemsPerPage = 10;

const filteredFranchises = computed(() => {
  let filtered = franchises.value;

  // Apply search filter
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    filtered = filtered.filter(franchise =>
      franchise.name.toLowerCase().includes(search) ||
      franchise.location.toLowerCase().includes(search) ||
      franchise.manager.toLowerCase().includes(search)
    );
  }

  // Apply status filter
  if (filters.value.status) {
    filtered = filtered.filter(franchise => franchise.status === filters.value.status);
  }

  // Apply region filter
  if (filters.value.region) {
    filtered = filtered.filter(franchise => franchise.region === filters.value.region);
  }

  // Apply sorting
  filtered.sort((a, b) => {
    switch (filters.value.sort) {
      case 'name':
        return a.name.localeCompare(b.name);
      case 'revenue':
        return b.revenue - a.revenue;
      case 'created_at':
        return new Date(b.created_at) - new Date(a.created_at);
      case 'performance':
        return b.performance - a.performance;
      default:
        return 0;
    }
  });

  return filtered;
});

const totalPages = computed(() => {
  return Math.ceil(filteredFranchises.value.length / itemsPerPage);
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount);
};

const getPerformanceColor = (performance) => {
  if (performance >= 90) return 'bg-positive';
  if (performance >= 75) return 'bg-warning';
  return 'bg-danger';
};

const openAddModal = () => {
  notificationsStore.showInfo('Add Franchise', 'Add franchise functionality coming soon');
};

const viewFranchise = (franchise) => {
  notificationsStore.showInfo('View Franchise', `Viewing ${franchise.name} details`);
};

const editFranchise = (franchise) => {
  notificationsStore.showInfo('Edit Franchise', `Editing ${franchise.name}`);
};

const deleteFranchise = async (franchise) => {
  if (confirm(`Are you sure you want to delete ${franchise.name}?`)) {
    const index = franchises.value.findIndex(f => f.id === franchise.id);
    if (index > -1) {
      franchises.value.splice(index, 1);
      notificationsStore.showSuccess('Franchise Deleted', `${franchise.name} has been removed`);
    }
  }
};

const exportData = () => {
  notificationsStore.showSuccess('Export Started', 'Franchise data export has been initiated');
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

onMounted(() => {
  // Fetch franchises from API
  console.log('FranchiseManagement mounted');
});
</script>
