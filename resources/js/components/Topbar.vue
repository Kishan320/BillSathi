<template>
  <header class="h-16 bg-card border-b border-border flex items-center px-4 lg:px-6 gap-4 flex-shrink-0 z-20 relative">
    <!-- Mobile menu button -->
    <button
      class="lg:hidden text-muted-foreground hover:text-foreground transition-colors"
      @click="$emit('mobileMenuOpen')"
      aria-label="Open menu"
    >
      <AppIcon name="menu" :size="20" />
    </button>

    <!-- Mobile logo -->
    <div class="lg:hidden flex items-center gap-2">
      <AppLogo :size="26" />
      <span class="font-bold text-sm">Hisaab</span>
    </div>

    <!-- Search -->
    <div
      :class="[
        'hidden sm:flex items-center gap-2 bg-muted rounded-lg px-3 py-2 transition-all flex-1 max-w-md',
        searchFocused ? 'ring-2 ring-primary/30 bg-card' : ''
      ]"
    >
      <AppIcon name="search" :size="14" class="text-muted-foreground flex-shrink-0" />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search franchises, locations, reports…"
        class="bg-transparent text-sm text-foreground placeholder:text-muted-foreground outline-none flex-1 min-w-0"
        @focus="searchFocused = true"
        @blur="searchFocused = false"
        @keydown.enter="handleSearch"
      />
      <div class="hidden md:flex items-center gap-1 text-muted-foreground">
        <AppIcon name="command" :size="10" />
        <span class="text-[10px] font-medium">K</span>
      </div>
    </div>

    <div class="flex-1" />

    <!-- Right actions -->
    <div class="flex items-center gap-2">
      <!-- AI indicator -->
      <div class="hidden md:flex items-center gap-1.5 bg-violet-bg text-violet px-3 py-1.5 rounded-lg text-xs font-semibold">
        <AppIcon name="sparkles" :size="12" />
        <span>AI Active</span>
      </div>

      <!-- Language Switcher -->
      <div class="relative" ref="langDropdownRef">
        <button
          class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-border hover:bg-muted transition-colors"
          @click="langOpen = !langOpen"
          aria-label="Switch language"
        >
          <span>{{ currentLang.flag }}</span>
          <span class="hidden sm:inline text-foreground">{{ currentLang.label }}</span>
          <AppIcon name="chevron-down" :size="10" class="text-muted-foreground" />
        </button>

        <div
          v-if="langOpen"
          class="absolute right-0 top-full mt-2 w-36 bg-card border border-border rounded-xl shadow-card-lg z-50 overflow-hidden"
        >
          <button
            v-for="lang in languages"
            :key="lang.code"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-muted transition-colors"
            :class="locale === lang.code ? 'text-primary font-semibold' : 'text-foreground'"
            @click="switchLang(lang.code)"
          >
            <span>{{ lang.flag }}</span>
            <span>{{ lang.label }}</span>
          </button>
        </div>
      </div>

      <!-- Help -->
      <button
        class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
        aria-label="Help"
        @click="openHelp"
      >
        <AppIcon name="help-circle" :size="16" />
      </button>

      <!-- Notifications -->
      <div class="relative">
        <button
          class="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors relative"
          @click="toggleNotifications"
          aria-label="Notifications"
        >
          <AppIcon name="bell" :size="16" />
          <span
            v-if="notificationsStore.hasUnread"
            class="absolute top-1 right-1 w-2 h-2 bg-danger rounded-full border border-card"
          />
        </button>

        <!-- Notifications dropdown -->
        <div
          v-if="notificationsStore.notificationOpen"
          class="absolute right-0 top-full mt-2 w-80 bg-card border border-border rounded-xl shadow-card-lg z-50 fade-in overflow-hidden"
        >
          <div class="flex items-center justify-between px-4 py-3 border-b border-border">
            <span class="text-sm font-semibold">Notifications</span>
            <span class="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
              {{ notificationsStore.unreadCount }} unread
            </span>
          </div>
          
          <ul class="max-h-72 overflow-y-auto scrollbar-thin">
            <li
              v-for="notification in notificationsStore.recentNotifications"
              :key="notification.id"
              class="px-4 py-3 border-b border-border last:border-0 hover:bg-muted/50 cursor-pointer transition-colors"
              @click="markAsRead(notification.id)"
            >
              <div class="flex items-start gap-3">
                <span
                  :class="[
                    'mt-0.5 w-2 h-2 rounded-full flex-shrink-0',
                    getStatusClass(notification.type)
                  ]"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-foreground leading-tight">
                    {{ notification.title }}
                  </p>
                  <p class="text-xs text-muted-foreground mt-0.5">
                    {{ notification.description }}
                  </p>
                </div>
                <span class="text-[10px] text-muted-foreground whitespace-nowrap">
                  {{ notification.time }}
                </span>
              </div>
            </li>
          </ul>
          
          <div class="px-4 py-2 border-t border-border">
            <button
              class="text-xs text-primary font-medium hover:underline"
              @click="viewAllNotifications"
            >
              View all notifications
            </button>
          </div>
        </div>
      </div>

      <!-- User -->
      <button
        class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg hover:bg-muted transition-colors"
        @click="toggleUserMenu"
      >
        <div class="w-7 h-7 rounded-full gradient-primary flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
          {{ userInitials }}
        </div>
        <div class="hidden md:block text-left">
          <p class="text-xs font-semibold text-foreground leading-tight">
            {{ authStore.currentUser?.name || 'Marcus Webb' }}
          </p>
          <p class="text-[10px] text-muted-foreground">
            {{ authStore.currentUser?.role || 'Super Admin' }}
          </p>
        </div>
        <AppIcon name="chevron-down" :size="12" class="text-muted-foreground hidden md:block" />
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import { useNotificationsStore } from '@/stores/notifications';
import AppLogo from '@/components/ui/AppLogo.vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const emit = defineEmits(['mobileMenuOpen']);

const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();
const { locale } = useI18n();

const searchFocused = ref(false);
const searchQuery = ref('');
const langOpen = ref(false);
const langDropdownRef = ref(null);

const languages = [
  { code: 'en', label: 'English', flag: '🇬🇧' },
  { code: 'hi', label: 'हिंदी', flag: '🇮🇳' },
  { code: 'gu', label: 'ગુજરાતી', flag: '🇮🇳' },
];

const currentLang = computed(() => languages.find(l => l.code === locale.value) || languages[0]);

const switchLang = async (code) => {
  locale.value = code;
  langOpen.value = false;
  try {
    await authStore.updateLang(code);
  } catch {
    // silently ignore if not authenticated
    localStorage.setItem('lang', code);
  }
};

const userInitials = computed(() => {
  const name = authStore.currentUser?.name || 'Marcus Webb';
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2);
});

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    console.log('Searching for:', searchQuery.value);
  }
};

const toggleNotifications = () => notificationsStore.toggleNotifications();
const markAsRead = (id) => notificationsStore.markAsRead(id);
const viewAllNotifications = () => {
  console.log('Navigate to all notifications');
  notificationsStore.closeNotifications();
};
const toggleUserMenu = () => console.log('Toggle user menu');
const openHelp = () => console.log('Open help');

const getStatusClass = (type) => {
  const map = { warning: 'bg-warning', danger: 'bg-danger', success: 'bg-positive', info: 'bg-info' };
  return map[type] || 'bg-info';
};

const handleClickOutside = (event) => {
  if (langDropdownRef.value && !langDropdownRef.value.contains(event.target)) {
    langOpen.value = false;
  }
  if (!event.target.closest('.relative')) {
    notificationsStore.closeNotifications();
  }
};

onMounted(() => {
  notificationsStore.fetchNotifications();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
