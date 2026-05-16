import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || null);
  const refreshToken = ref(localStorage.getItem('refreshToken') || null);
  const loading = ref(false);
  const error = ref(null);
  let fetchUserRequest = null;
  let refreshRequest = null;

  // Getters
  const isAuthenticated = computed(() => !!token.value);
  const currentUser = computed(() => user.value);
  const userRole = computed(() => user.value?.role || null);
  const userPermissions = computed(() => user.value?.permissions || []);

  // Actions
  const login = async (credentials) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.post('/auth/login', credentials);
      const { access_token, refresh_token, user: userData } = response.data;
      
      token.value = access_token;
      refreshToken.value = refresh_token;
      user.value = userData;
      
      localStorage.setItem('token', access_token);
      localStorage.setItem('refreshToken', refresh_token);
      localStorage.setItem('user', JSON.stringify(userData));
      if (userData.lang) localStorage.setItem('lang', userData.lang);
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const register = async (userData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.post('/auth/register', userData);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const logout = async () => {
    if (!token.value) {
      clearAuth();
      return;
    }

    loading.value = true;
    
    try {
      await api.post('/auth/logout');
    } catch (err) {
      console.error('Logout error:', err);
    } finally {
      clearAuth();
      loading.value = false;
    }
  };

  const refreshAccessToken = async () => {
    if (!refreshToken.value) {
      throw new Error('No refresh token available');
    }
    if (refreshRequest) return refreshRequest;
    
    refreshRequest = api.post('/auth/refresh', {
      refresh_token: refreshToken.value
    })
      .then((response) => {
        const { access_token, refresh_token: newRefreshToken } = response.data;
        token.value = access_token;
        refreshToken.value = newRefreshToken;

        localStorage.setItem('token', access_token);
        localStorage.setItem('refreshToken', newRefreshToken);

        return access_token;
      })
      .catch((err) => {
        clearAuth();
        throw err;
      })
      .finally(() => {
        refreshRequest = null;
      });

    return refreshRequest;
  };

  const fetchUser = async ({ force = false } = {}) => {
    if (!token.value) return;
    if (user.value && !force) return user.value;
    if (fetchUserRequest) return fetchUserRequest;
    
    loading.value = true;
    
    fetchUserRequest = api.get('/auth/user')
      .then((response) => {
        user.value = response.data;
        localStorage.setItem('user', JSON.stringify(response.data));
        return user.value;
      })
      .catch((err) => {
        console.error('Failed to fetch user:', err);
        if (err.response?.status === 401) {
          clearAuth();
        }
        throw err;
      })
      .finally(() => {
        loading.value = false;
        fetchUserRequest = null;
      });

    return fetchUserRequest;
  };

  const updateProfile = async (profileData) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.put('/auth/profile', profileData);
      user.value = { ...user.value, ...response.data };
      localStorage.setItem('user', JSON.stringify(user.value));
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Profile update failed';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateLang = async (lang) => {
    await api.put('/auth/lang', { lang });
    user.value = { ...user.value, lang };
    localStorage.setItem('user', JSON.stringify(user.value));
    localStorage.setItem('lang', lang);
  };

  const clearAuth = () => {
    user.value = null;
    token.value = null;
    refreshToken.value = null;
    error.value = null;
    fetchUserRequest = null;
    refreshRequest = null;
    
    localStorage.removeItem('token');
    localStorage.removeItem('refreshToken');
    localStorage.removeItem('user');
  };

  const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
  };

  const hasRole = (role) => {
    return userRole.value === role;
  };

  const initializeAuth = () => {
    const savedUser = localStorage.getItem('user');
    if (savedUser && token.value) {
      try {
        user.value = JSON.parse(savedUser);
        if (user.value?.lang) localStorage.setItem('lang', user.value.lang);
      } catch (err) {
        console.error('Failed to parse saved user data:', err);
        clearAuth();
      }
    }
  };

  return {
    // State
    user,
    token,
    refreshToken,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    currentUser,
    userRole,
    userPermissions,
    
    // Actions
    login,
    register,
    logout,
    refreshAccessToken,
    fetchUser,
    updateProfile,
    updateLang,
    clearAuth,
    hasPermission,
    hasRole,
    initializeAuth
  };
});
