<template>
  <div class="h-full flex-shrink-0">
    <!-- Desktop Sidebar -->
    <aside
      :class="[
        'hidden lg:flex h-full min-h-0 flex-col bg-card border-r border-border sidebar-transition flex-shrink-0 relative z-30',
        sidebarWidth
      ]"
    >
      <!-- Logo -->
      <div
        :class="[
          'flex items-center h-16 border-b border-border px-3 flex-shrink-0',
          collapsed ? 'justify-center' : 'justify-between'
        ]"
      >
        <div v-if="!collapsed" class="flex items-center gap-2">
          <AppLogo :size="32" />
          <span class="font-bold text-base text-foreground tracking-tight">Hisaab</span>
        </div>
        <AppLogo v-if="collapsed" :size="32" />
        
        <button
          v-if="!collapsed"
          @click="$emit('toggleCollapse')"
          class="w-6 h-6 flex items-center justify-center rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
          aria-label="Collapse sidebar"
        >
          <AppIcon name="chevron-left" :size="14" />
        </button>
      </div>

      <!-- Expand button when collapsed -->
      <button
        v-if="collapsed"
        @click="$emit('toggleCollapse')"
        class="mx-auto mt-2 w-8 h-8 flex items-center justify-center rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
        aria-label="Expand sidebar"
      >
        <AppIcon name="chevron-right" :size="14" />
      </button>

      <nav class="flex-1 min-h-0 overflow-y-auto scrollbar-thin py-2">
        <ul>
          <li v-for="item in navItems" :key="item.id">
            <!-- Group with children -->
            <template v-if="item.group">
              <button
                @click="toggleGroup(item.id)"
                :class="[
                  'w-full flex items-center gap-3 px-3 py-2 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                  item.children.some(c => isActive(c.href)) ? 'nav-item-active font-semibold' : 'text-muted-foreground',
                  collapsed ? 'justify-center' : ''
                ]"
                :style="collapsed ? '' : 'width: calc(100% - 1rem)'"
              >
                <AppIcon :name="item.icon" :size="16" class="flex-shrink-0" />
                <span v-if="!collapsed" class="flex-1 truncate text-left">{{ item.label }}</span>
                <AppIcon v-if="!collapsed" :name="expandedGroups.includes(item.id) ? 'chevron-down' : 'chevron-right'" :size="12" class="flex-shrink-0" />
              </button>
              <ul v-if="!collapsed && expandedGroups.includes(item.id)" class="ml-4 mt-0.5 space-y-0.5">
                <li v-for="child in item.children" :key="child.id">
                  <router-link
                    :to="child.href"
                    :class="[
                      'flex items-center gap-3 px-3 py-1.5 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                      isActive(child.href) ? 'nav-item-active font-semibold' : 'text-muted-foreground'
                    ]"
                  >
                    <AppIcon :name="child.icon" :size="14" class="flex-shrink-0" />
                    <span class="flex-1 truncate">{{ child.label }}</span>
                  </router-link>
                </li>
              </ul>
            </template>
            <!-- Regular item -->
            <template v-else>
              <router-link
                :to="item.href"
                :class="[
                  'flex items-center gap-3 px-3 py-2 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                  isActive(item.href) ? 'nav-item-active font-semibold' : 'text-muted-foreground',
                  collapsed ? 'justify-center' : ''
                ]"
                :title="collapsed ? item.label : undefined"
              >
                <AppIcon :name="item.icon" :size="16" class="flex-shrink-0" />
                <span v-if="!collapsed" class="flex-1 truncate">{{ item.label }}</span>
              </router-link>
            </template>
          </li>
        </ul>
      </nav>

      <!-- User profile -->
      <div
        :class="[
          'border-t border-border p-3 flex items-center gap-3 flex-shrink-0',
          collapsed ? 'justify-center' : ''
        ]"
      >
        <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
          {{ userInitials }}
        </div>
        
        <div v-if="!collapsed" class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-foreground truncate">{{ userName }}</p>
          <p class="text-xs text-muted-foreground truncate">{{ userRole }}</p>
        </div>
        
        <button
          v-if="!collapsed"
          @click="handleLogout"
          class="text-muted-foreground hover:text-danger transition-colors"
          aria-label="Sign out"
        >
          <AppIcon name="log-out" :size="14" />
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar -->
    <aside
      :class="[
        'lg:hidden fixed inset-y-0 left-0 z-50 w-64 bg-card border-r border-border flex min-h-0 flex-col sidebar-transition',
        mobileOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex items-center justify-between h-16 border-b border-border px-4">
        <div class="flex items-center gap-2">
          <AppLogo :size="28" />
          <span class="font-bold text-base text-foreground">Hisaab</span>
        </div>
        <button
          @click="$emit('mobileClose')"
          class="text-muted-foreground hover:text-foreground"
          aria-label="Close menu"
        >
          <AppIcon name="x" :size="18" />
        </button>
      </div>
      
      <nav class="flex-1 min-h-0 overflow-y-auto scrollbar-thin py-2">
        <ul>
          <li v-for="item in navItems" :key="`mob-${item.id}`">
            <template v-if="item.group">
              <button
                @click="toggleGroup(item.id)"
                :class="[
                  'w-full flex items-center gap-3 px-3 py-2 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                  item.children.some(c => isActive(c.href)) ? 'nav-item-active font-semibold' : 'text-muted-foreground'
                ]"
                style="width: calc(100% - 1rem)"
              >
                <AppIcon :name="item.icon" :size="16" class="flex-shrink-0" />
                <span class="flex-1 truncate text-left">{{ item.label }}</span>
                <AppIcon :name="expandedGroups.includes(item.id) ? 'chevron-down' : 'chevron-right'" :size="12" class="flex-shrink-0" />
              </button>
              <ul v-if="expandedGroups.includes(item.id)" class="ml-4 mt-0.5 space-y-0.5">
                <li v-for="child in item.children" :key="`mob-${child.id}`">
                  <router-link
                    :to="child.href"
                    @click="$emit('mobileClose')"
                    :class="[
                      'flex items-center gap-3 px-3 py-1.5 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                      isActive(child.href) ? 'nav-item-active font-semibold' : 'text-muted-foreground'
                    ]"
                  >
                    <AppIcon :name="child.icon" :size="14" class="flex-shrink-0" />
                    <span class="flex-1">{{ child.label }}</span>
                  </router-link>
                </li>
              </ul>
            </template>
            <template v-else>
              <router-link
                :to="item.href"
                @click="$emit('mobileClose')"
                :class="[
                  'flex items-center gap-3 px-3 py-2 mx-2 rounded-lg text-sm font-medium transition-all nav-item-hover',
                  isActive(item.href) ? 'nav-item-active font-semibold' : 'text-muted-foreground'
                ]"
              >
                <AppIcon :name="item.icon" :size="16" class="flex-shrink-0" />
                <span class="flex-1">{{ item.label }}</span>
              </router-link>
            </template>
          </li>
        </ul>
      </nav>
      
      <div class="border-t border-border p-3 flex items-center gap-3 flex-shrink-0">
        <div class="w-8 h-8 rounded-full gradient-primary flex items-center justify-center text-white text-xs font-bold">
          {{ userInitials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold truncate">{{ userName }}</p>
          <p class="text-xs text-muted-foreground">{{ userRole }}</p>
        </div>
        <button @click="handleLogout" class="text-muted-foreground">
          <AppIcon name="log-out" :size="14" />
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppLogo from '@/components/ui/AppLogo.vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  },
  mobileOpen: {
    type: Boolean,
    default: false
  },
  currentPath: {
    type: String,
    default: '/'
  }
});

const emit = defineEmits(['toggleCollapse', 'mobileClose']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const expandedGroups = ref(['purchases']);

const toggleGroup = (group) => {
  const index = expandedGroups.value.indexOf(group);
  if (index > -1) expandedGroups.value.splice(index, 1);
  else expandedGroups.value.push(group);
};

const getIconName = (n) => n.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();

const navItems = [
  { id: 'dashboard',        label: 'Dashboard',        icon: 'layout-dashboard',  href: '/dashboard' },
  { id: 'reports',          label: 'Reports',           icon: 'bar-chart-3',       href: '/reports' },
  { id: 'contacts',         label: 'Contacts',          icon: 'phone',             href: '/contacts' },
  { id: 'vendors',          label: 'Vendors',           icon: 'truck',             href: '/vendors' },
  { id: 'bank-accounts',    label: 'Bank Accounts',     icon: 'landmark',          href: '/bank-accounts' },
  { id: 'expenses',         label: 'Expenses',          icon: 'receipt',           href: '/expenses' },
  { id: 'incomes',          label: 'Incomes',           icon: 'trending-up',       href: '/incomes' },
  { id: 'payments',         label: 'Payments',          icon: 'credit-card',       href: '/payments' },
  { id: 'bank-transfers',   label: 'Bank Transfers',    icon: 'arrow-left-right',  href: '/bank-transfers' },
  { id: 'loans',            label: 'Loans & Advances',  icon: 'hand-coins',        href: '/loans' },
  { id: 'items',            label: 'Items',             icon: 'shopping-basket',   href: '/items' },
  {
    id: 'purchases-group', label: 'Purchases', icon: 'shopping-cart', group: true,
    children: [
      { id: 'purchases',   label: 'Purchase Invoices', icon: 'file-text',   href: '/purchases' },
      { id: 'warehouses',  label: 'Warehouses',        icon: 'warehouse',   href: '/warehouses' },
    ]
  },
  { id: 'sales',            label: 'Sales',             icon: 'tag',               href: '/sales' },
  { id: 'settlements',      label: 'Settlements',       icon: 'handshake',         href: '/settlements' },
  { id: 'journal-vouchers', label: 'Journal Vouchers',  icon: 'notebook-pen',      href: '/journal-vouchers' },
  { id: 'custom-accounts',  label: 'Custom Accounts',   icon: 'sliders',           href: '/custom-accounts' },
  { id: 'settings',         label: 'Settings',          icon: 'settings',          href: '/settings' },
  { id: 'team',             label: 'Team',              icon: 'users',             href: '/team' },
  { id: 'subscription',     label: 'Subscription',      icon: 'badge-check',       href: '/subscription' },
];

const sidebarWidth = computed(() => {
  return props.collapsed ? 'w-16' : 'w-60';
});

const userName = computed(() => {
  return authStore.currentUser?.name || 'Marcus Webb';
});

const userRole = computed(() => {
  return authStore.currentUser?.role || 'Super Admin';
});

const userInitials = computed(() => {
  const name = userName.value;
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

const isActive = (href) => {
  if (href === '/dashboard') {
    return route.path === '/dashboard' || route.path === '/';
  }
  return route.path === href || route.path.startsWith(href + '/');
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
