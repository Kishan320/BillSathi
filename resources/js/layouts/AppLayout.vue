<template>
  <div class="flex h-screen bg-background overflow-hidden">
    <!-- Mobile overlay -->
    <div
      v-if="themeStore.mobileSidebarOpen"
      class="fixed inset-0 bg-foreground/20 backdrop-blur-sm z-40 lg:hidden"
      @click="themeStore.closeMobileSidebar"
    />

    <!-- Sidebar -->
    <Sidebar
      :collapsed="themeStore.sidebarCollapsed"
      @toggle-collapse="themeStore.toggleSidebar"
      :mobile-open="themeStore.mobileSidebarOpen"
      @mobile-close="themeStore.closeMobileSidebar"
      :current-path="$route.path"
    />

    <!-- Main area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <Topbar
        @mobile-menu-open="themeStore.openMobileSidebar"
        :sidebar-collapsed="themeStore.sidebarCollapsed"
      />
      <main class="flex-1 overflow-y-auto scrollbar-thin">
        <div class="max-w-screen-2xl mx-auto px-6 lg:px-8 xl:px-10 2xl:px-12 py-8">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';
import Sidebar from '@/components/Sidebar.vue';
import Topbar from '@/components/Topbar.vue';

const themeStore = useThemeStore();
const authStore = useAuthStore();
const { locale } = useI18n();

onMounted(() => {
  themeStore.initializeTheme();
  authStore.initializeAuth();
  const lang = authStore.currentUser?.lang || localStorage.getItem('lang') || 'en';
  locale.value = lang;
});
</script>
