import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import { useApiCacheStore } from '@/stores/apiCache';
import { useNotificationsStore } from '@/stores/notifications';

// Base API configuration
const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

// Create axios instance
export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    
    // Add auth token if available
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    
    // Add request timestamp for debugging
    config.metadata = { startTime: new Date() };
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    // Log request duration for debugging
    const endTime = new Date();
    const duration = endTime - response.config.metadata.startTime;
    console.log(`API Request to ${response.config.url} took ${duration}ms`);
    
    return response;
  },
  async (error) => {
    const authStore = useAuthStore();
    const notificationsStore = useNotificationsStore();
    
    const originalRequest = error.config;
    
    // Handle network errors
    if (!error.response) {
      notificationsStore.showError('Network Error', 'Unable to connect to the server');
      return Promise.reject(error);
    }
    
    const { status, data } = error.response;
    
    // Handle 401 Unauthorized
    if (status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        // Try to refresh the token
        await authStore.refreshAccessToken();
        
        // Retry the original request with new token
        return api(originalRequest);
      } catch (refreshError) {
        // Refresh failed, logout user
        authStore.clearAuth();
        notificationsStore.showError('Session Expired', 'Please login again');
        
        // Redirect to login page
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
        
        return Promise.reject(refreshError);
      }
    }
    
    // Handle 403 Forbidden
    if (status === 403) {
      notificationsStore.showError('Access Denied', 'You don\'t have permission to perform this action');
      return Promise.reject(error);
    }
    
    // Handle 404 Not Found
    if (status === 404) {
      notificationsStore.showError('Not Found', 'The requested resource was not found');
      return Promise.reject(error);
    }
    
    // Handle 422 Validation Error
    if (status === 422) {
      const validationErrors = data.errors || {};
      const firstError = Object.values(validationErrors)[0]?.[0] || data.message;
      notificationsStore.showError('Validation Error', firstError);
      return Promise.reject(error);
    }
    
    // Handle 500 Server Error
    if (status >= 500) {
      notificationsStore.showError('Server Error', 'Something went wrong on our end. Please try again later.');
      return Promise.reject(error);
    }
    
    // Handle other errors
    const errorMessage = data.message || 'An unexpected error occurred';
    notificationsStore.showError('Error', errorMessage);
    
    return Promise.reject(error);
  }
);

const cachedGet = (url, config = {}) => {
  const cacheStore = useApiCacheStore();
  return cacheStore.remember('get', url, config, () => api.get(url, config));
};

const forgetCachedGets = () => {
  const cacheStore = useApiCacheStore();
  cacheStore.forget();
};

const mutate = async (request) => {
  const response = await request();
  forgetCachedGets();
  return response;
};

const crudResource = (path) => ({
  getAll: (params = {}) => cachedGet(path, { params }),
  getById: (id) => cachedGet(`${path}/${id}`),
  create: (data) => mutate(() => api.post(path, data)),
  update: (id, data) => mutate(() => api.put(`${path}/${id}`, data)),
  delete: (id) => mutate(() => api.delete(`${path}/${id}`)),
});

// API service methods
export const apiService = {
  // Auth endpoints
  auth: {
    login: (credentials) => mutate(() => api.post('/auth/login', credentials)),
    register: (userData) => mutate(() => api.post('/auth/register', userData)),
    logout: () => mutate(() => api.post('/auth/logout')),
    refresh: (refreshToken) => mutate(() => api.post('/auth/refresh', { refresh_token: refreshToken })),
    user: () => cachedGet('/auth/user'),
    updateProfile: (profileData) => mutate(() => api.put('/auth/profile', profileData)),
    changePassword: (passwordData) => mutate(() => api.put('/auth/password', passwordData)),
    forgotPassword: (email) => mutate(() => api.post('/auth/forgot-password', { email })),
    resetPassword: (token, password) => mutate(() => api.post('/auth/reset-password', { token, password })),
  },
  
  // Dashboard endpoints
  dashboard: {
    get: () => cachedGet('/dashboard'),
    getStats: () => cachedGet('/dashboard/stats'),
    getCharts: () => cachedGet('/dashboard/charts'),
    getRecentActivity: () => cachedGet('/dashboard/activity'),
  },
  
  // Franchise management endpoints
  franchises: {
    getAll: (params = {}) => cachedGet('/franchises', { params }),
    getById: (id) => cachedGet(`/franchises/${id}`),
    create: (data) => mutate(() => api.post('/franchises', data)),
    update: (id, data) => mutate(() => api.put(`/franchises/${id}`, data)),
    delete: (id) => mutate(() => api.delete(`/franchises/${id}`)),
    getLocations: (id) => cachedGet(`/franchises/${id}/locations`),
    getPerformance: (id) => cachedGet(`/franchises/${id}/performance`),
  },
  
  // Users endpoints
  users: {
    getAll: (params = {}) => cachedGet('/users', { params }),
    getById: (id) => cachedGet(`/users/${id}`),
    create: (data) => mutate(() => api.post('/users', data)),
    update: (id, data) => mutate(() => api.put(`/users/${id}`, data)),
    delete: (id) => mutate(() => api.delete(`/users/${id}`)),
    getRoles: () => cachedGet('/users/roles'),
  },
  
  // Settings endpoints
  settings: {
    get: () => cachedGet('/settings'),
    update: (data) => mutate(() => api.put('/settings', data)),
    getPermissions: () => cachedGet('/settings/permissions'),
  },
  
  // Notifications endpoints
  notifications: {
    getAll: (params = {}) => cachedGet('/notifications', { params }),
    markAsRead: (id) => mutate(() => api.put(`/notifications/${id}/read`)),
    markAllAsRead: () => mutate(() => api.put('/notifications/read-all')),
    delete: (id) => mutate(() => api.delete(`/notifications/${id}`)),
  },

  contacts: crudResource('/contacts'),
  bankAccounts: crudResource('/bank-accounts'),
  expenses: crudResource('/expenses'),
  incomes: crudResource('/incomes'),
  payments: crudResource('/payments'),
  bankTransfers: crudResource('/bank-transfers'),
  loans: crudResource('/loans'),
  items: {
    ...crudResource('/items'),
    generateSku: () => api.get('/items/generate-sku'),
  },
  purchases: crudResource('/purchases'),
  sales: crudResource('/sales'),
  settlements: crudResource('/settlements'),
  journalVouchers: crudResource('/journal-vouchers'),
  customAccounts: crudResource('/custom-accounts'),
  systemSettings: {
    get: () => cachedGet('/system-settings'),
    create: (data) => mutate(() => api.post('/system-settings', data)),
    update: (id, data) => mutate(() => api.put(`/system-settings/${id}`, data)),
    delete: (id) => mutate(() => api.delete(`/system-settings/${id}`)),
  },
};

// Utility functions
export const createFormData = (data) => {
  const formData = new FormData();
  
  Object.keys(data).forEach(key => {
    if (data[key] !== null && data[key] !== undefined) {
      if (data[key] instanceof File) {
        formData.append(key, data[key]);
      } else if (typeof data[key] === 'object') {
        formData.append(key, JSON.stringify(data[key]));
      } else {
        formData.append(key, data[key]);
      }
    }
  });
  
  return formData;
};

export const downloadFile = async (url, filename) => {
  try {
    const response = await cachedGet(url, { responseType: 'blob' });
    const blob = new Blob([response.data]);
    const downloadUrl = window.URL.createObjectURL(blob);
    
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    window.URL.revokeObjectURL(downloadUrl);
  } catch (error) {
    console.error('Download failed:', error);
    throw error;
  }
};

export default api;
