<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Activity -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-foreground">Recent Activity</h3>
        <button
          @click="viewAllActivity"
          class="text-sm text-primary hover:underline"
        >
          View all
        </button>
      </div>
      
      <div class="space-y-4">
        <div
          v-for="activity in recentActivities"
          :key="activity.id"
          class="flex items-start gap-3"
        >
          <div
            :class="[
              'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
              getActivityIconClass(activity.type)
            ]"
          >
            <AppIcon :name="getActivityIcon(activity.type)" :size="14" class="text-white" />
          </div>
          
          <div class="flex-1 min-w-0">
            <p class="text-sm text-foreground">{{ activity.title }}</p>
            <p class="text-xs text-muted-foreground">{{ activity.description }}</p>
            <p class="text-xs text-muted-foreground mt-1">{{ activity.time }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-foreground">Upcoming Tasks</h3>
        <span class="text-xs bg-warning-bg text-warning px-2 py-1 rounded-full">
          {{ upcomingTasks.length }} pending
        </span>
      </div>
      
      <div class="space-y-3">
        <div
          v-for="task in upcomingTasks"
          :key="task.id"
          class="flex items-center gap-3 p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors"
        >
          <div
            :class="[
              'w-2 h-2 rounded-full flex-shrink-0',
              getPriorityClass(task.priority)
            ]"
          />
          
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-foreground">{{ task.title }}</p>
            <div class="flex items-center gap-2 mt-1">
              <AppIcon name="calendar" :size="10" class="text-muted-foreground" />
              <p class="text-xs text-muted-foreground">{{ task.dueDate }}</p>
            </div>
          </div>
          
          <button
            @click="completeTask(task.id)"
            class="text-muted-foreground hover:text-positive transition-colors"
          >
            <AppIcon name="check" :size="14" />
          </button>
        </div>
      </div>
    </div>

    <!-- System Status -->
    <div class="bg-card border border-border rounded-xl p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-foreground">System Status</h3>
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-positive animate-pulse" />
          <span class="text-xs text-positive">All systems operational</span>
        </div>
      </div>
      
      <div class="space-y-3">
        <div
          v-for="system in systemStatus"
          :key="system.name"
          class="flex items-center justify-between p-2 rounded-lg bg-muted/30"
        >
          <div class="flex items-center gap-2">
            <AppIcon :name="system.icon" :size="14" class="text-muted-foreground" />
            <span class="text-sm text-foreground">{{ system.name }}</span>
          </div>
          
          <div class="flex items-center gap-2">
            <span
              :class="[
                'text-xs font-medium',
                getStatusClass(system.status)
              ]"
            >
              {{ system.status }}
            </span>
            <div
              :class="[
                'w-1.5 h-1.5 rounded-full',
                getStatusDotClass(system.status)
              ]"
            />
          </div>
        </div>
      </div>
      
      <div class="mt-4 p-3 rounded-lg bg-info-bg text-info">
        <div class="flex items-start gap-2">
          <AppIcon name="info" :size="14" />
          <div>
            <p class="text-xs font-medium">System Update Scheduled</p>
            <p class="text-xs mt-0.5">Next maintenance window: Sunday 2:00 AM EST</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppIcon from '@/components/ui/AppIcon.vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const recentActivities = ref([
  {
    id: 1,
    type: 'franchise',
    title: 'New franchise application',
    description: 'Diego Reyes — Denver, CO',
    time: '2 hours ago'
  },
  {
    id: 2,
    type: 'revenue',
    title: 'Monthly target achieved',
    description: 'NYC Midtown exceeded $148K target',
    time: '4 hours ago'
  },
  {
    id: 3,
    type: 'compliance',
    title: 'Compliance review completed',
    description: 'Miami Beach location approved',
    time: '6 hours ago'
  },
  {
    id: 4,
    type: 'user',
    title: 'New user registered',
    description: 'Sarah Johnson joined as Manager',
    time: '1 day ago'
  }
]);

const upcomingTasks = ref([
  {
    id: 1,
    title: 'Review Q3 financial reports',
    dueDate: 'Tomorrow',
    priority: 'high'
  },
  {
    id: 2,
    title: 'Update franchise documentation',
    dueDate: 'Oct 28',
    priority: 'medium'
  },
  {
    id: 3,
    title: 'Schedule compliance audit',
    dueDate: 'Oct 30',
    priority: 'low'
  }
]);

const systemStatus = ref([
  {
    name: 'API Server',
    icon: 'server',
    status: 'Operational'
  },
  {
    name: 'Database',
    icon: 'database',
    status: 'Operational'
  },
  {
    name: 'Payment Gateway',
    icon: 'credit-card',
    status: 'Operational'
  },
  {
    name: 'Email Service',
    icon: 'mail',
    status: 'Degraded'
  }
]);

const getActivityIcon = (type) => {
  const iconMap = {
    franchise: 'building-2',
    revenue: 'trending-up',
    compliance: 'shield-check',
    user: 'users'
  };
  return iconMap[type] || 'info';
};

const getActivityIconClass = (type) => {
  const classMap = {
    franchise: 'bg-primary',
    revenue: 'bg-positive',
    compliance: 'bg-warning',
    user: 'bg-info'
  };
  return classMap[type] || 'bg-muted';
};

const getPriorityClass = (priority) => {
  const classMap = {
    high: 'bg-danger',
    medium: 'bg-warning',
    low: 'bg-info'
  };
  return classMap[priority] || 'bg-muted';
};

const getStatusClass = (status) => {
  const classMap = {
    Operational: 'text-positive',
    Degraded: 'text-warning',
    Down: 'text-danger'
  };
  return classMap[status] || 'text-muted-foreground';
};

const getStatusDotClass = (status) => {
  const classMap = {
    Operational: 'bg-positive',
    Degraded: 'bg-warning',
    Down: 'bg-danger'
  };
  return classMap[status] || 'bg-muted-foreground';
};

const viewAllActivity = () => {
  router.push('/activity');
};

const completeTask = (taskId) => {
  const index = upcomingTasks.value.findIndex(task => task.id === taskId);
  if (index > -1) {
    upcomingTasks.value.splice(index, 1);
  }
};

onMounted(() => {
  // Fetch recent activity, tasks, and system status
  console.log('DashboardBottomRow mounted');
});
</script>
