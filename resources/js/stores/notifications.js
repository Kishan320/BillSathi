import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);
  const notificationOpen = ref(false);

  // Getters
  const hasUnread = computed(() => unreadCount.value > 0);
  const recentNotifications = computed(() => 
    notifications.value.slice(0, 5)
  );
  const unreadNotifications = computed(() => 
    notifications.value.filter(n => !n.read)
  );

  // Actions
  const addNotification = (notification) => {
    const newNotification = {
      id: Date.now().toString(),
      read: false,
      timestamp: new Date(),
      ...notification
    };
    
    notifications.value.unshift(newNotification);
    unreadCount.value++;
    
    return newNotification;
  };

  const markAsRead = (notificationId) => {
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification && !notification.read) {
      notification.read = true;
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  };

  const markAllAsRead = () => {
    notifications.value.forEach(notification => {
      notification.read = true;
    });
    unreadCount.value = 0;
  };

  const removeNotification = (notificationId) => {
    const index = notifications.value.findIndex(n => n.id === notificationId);
    if (index > -1) {
      const notification = notifications.value[index];
      if (!notification.read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      notifications.value.splice(index, 1);
    }
  };

  const clearAll = () => {
    notifications.value = [];
    unreadCount.value = 0;
  };

  const toggleNotifications = () => {
    notificationOpen.value = !notificationOpen.value;
  };

  const closeNotifications = () => {
    notificationOpen.value = false;
  };

  // Convenience methods for different notification types
  const showSuccess = (title, description = '') => {
    return addNotification({
      type: 'success',
      title,
      description,
      icon: 'check-circle'
    });
  };

  const showError = (title, description = '') => {
    return addNotification({
      type: 'error',
      title,
      description,
      icon: 'x-circle'
    });
  };

  const showWarning = (title, description = '') => {
    return addNotification({
      type: 'warning',
      title,
      description,
      icon: 'alert-triangle'
    });
  };

  const showInfo = (title, description = '') => {
    return addNotification({
      type: 'info',
      title,
      description,
      icon: 'info'
    });
  };

  // Simulate fetching notifications from API
  const fetchNotifications = async () => {
    loading.value = true;
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Mock data - replace with actual API call
      const mockNotifications = [
        {
          id: 'notif-001',
          type: 'warning',
          title: 'Royalty overdue — Miami Beach',
          description: 'Payment 14 days past due',
          time: '2h ago',
          read: false
        },
        {
          id: 'notif-002',
          type: 'error',
          title: 'Compliance fail — Austin TX',
          description: 'Health inspection score: 62/100',
          time: '4h ago',
          read: false
        },
        {
          id: 'notif-003',
          type: 'info',
          title: 'New franchise application',
          description: 'Diego Reyes — Denver, CO',
          time: '6h ago',
          read: false
        },
        {
          id: 'notif-004',
          type: 'success',
          title: 'Monthly target hit — NYC Midtown',
          description: 'Revenue exceeded $148K target',
          time: '1d ago',
          read: true
        }
      ];
      
      notifications.value = mockNotifications;
      unreadCount.value = mockNotifications.filter(n => !n.read).length;
      
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
    } finally {
      loading.value = false;
    }
  };

  return {
    // State
    notifications,
    unreadCount,
    loading,
    notificationOpen,
    
    // Getters
    hasUnread,
    recentNotifications,
    unreadNotifications,
    
    // Actions
    addNotification,
    markAsRead,
    markAllAsRead,
    removeNotification,
    clearAll,
    toggleNotifications,
    closeNotifications,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    fetchNotifications
  };
});
