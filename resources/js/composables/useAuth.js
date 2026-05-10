import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationsStore } from '@/stores/notifications';

export function useAuth() {
  const router = useRouter();
  const authStore = useAuthStore();
  const notificationsStore = useNotificationsStore();

  const login = async (credentials) => {
    try {
      const response = await authStore.login(credentials);
      notificationsStore.showSuccess('Login Successful', 'Welcome back!');
      await router.push('/dashboard');
      return response;
    } catch (error) {
      console.error('Login failed:', error);
      throw error;
    }
  };

  const register = async (userData) => {
    try {
      const response = await authStore.register(userData);
      notificationsStore.showSuccess('Registration Successful', 'Your account has been created');
      await router.push('/login');
      return response;
    } catch (error) {
      console.error('Registration failed:', error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      await authStore.logout();
      notificationsStore.showInfo('Logged Out', 'You have been successfully logged out');
      await router.push('/login');
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  const hasRole = (role) => {
    return authStore.hasRole(role);
  };

  const hasPermission = (permission) => {
    return authStore.hasPermission(permission);
  };

  const can = (permission) => {
    return hasPermission(permission);
  };

  const is = (role) => {
    return hasRole(role);
  };

  return {
    // Computed properties
    isAuthenticated: computed(() => authStore.isAuthenticated),
    user: computed(() => authStore.currentUser),
    role: computed(() => authStore.userRole),
    permissions: computed(() => authStore.userPermissions),
    loading: computed(() => authStore.loading),
    error: computed(() => authStore?.error || null),

    // Methods
    login,
    register,
    logout,
    hasRole,
    hasPermission,
    can,
    is,
    updateProfile: authStore.updateProfile,
    fetchUser: authStore.fetchUser,
    clearAuth: authStore.clearAuth
  };
}
