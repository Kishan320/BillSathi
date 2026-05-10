import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useThemeStore = defineStore('theme', () => {
  // State
  const isDark = ref(localStorage.getItem('theme') === 'dark');
  const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
  const mobileSidebarOpen = ref(false);

  // Getters
  const currentTheme = computed(() => isDark.value ? 'dark' : 'light');
  const themeIcon = computed(() => isDark.value ? 'sun' : 'moon');

  // Actions
  const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    updateThemeClass();
  };

  const setTheme = (theme) => {
    isDark.value = theme === 'dark';
    localStorage.setItem('theme', theme);
    updateThemeClass();
  };

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString());
  };

  const setSidebarCollapsed = (collapsed) => {
    sidebarCollapsed.value = collapsed;
    localStorage.setItem('sidebarCollapsed', collapsed.toString());
  };

  const openMobileSidebar = () => {
    mobileSidebarOpen.value = true;
  };

  const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
  };

  const updateThemeClass = () => {
    const root = document.documentElement;
    if (isDark.value) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  };

  const initializeTheme = () => {
    updateThemeClass();
  };

  return {
    // State
    isDark,
    sidebarCollapsed,
    mobileSidebarOpen,
    
    // Getters
    currentTheme,
    themeIcon,
    
    // Actions
    toggleTheme,
    setTheme,
    toggleSidebar,
    setSidebarCollapsed,
    openMobileSidebar,
    closeMobileSidebar,
    initializeTheme
  };
});
