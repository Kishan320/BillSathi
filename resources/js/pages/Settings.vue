<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div>
      <h1 class="text-2xl font-bold text-foreground">Settings</h1>
      <p class="text-sm text-muted-foreground mt-0.5">Manage your account and application settings</p>
    </div>

    <!-- Settings content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Settings navigation -->
      <div class="lg:col-span-1">
        <nav class="space-y-1">
          <button
            v-for="section in settingsSections"
            :key="section.id"
            @click="activeSection = section.id"
            :class="[
              'w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-left',
              activeSection === section.id
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:text-foreground hover:bg-muted'
            ]"
          >
            <AppIcon :name="section.icon" :size="16" />
            {{ section.label }}
          </button>
        </nav>
      </div>

      <!-- Settings content -->
      <div class="lg:col-span-3">
        <!-- Profile Settings -->
        <div v-if="activeSection === 'profile'" class="bg-card border border-border rounded-xl p-6">
          <h2 class="text-lg font-semibold text-foreground mb-6">Profile Settings</h2>
          
          <form @submit.prevent="updateProfile" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-foreground mb-2">First Name</label>
                <input
                  v-model="profileForm.firstName"
                  type="text"
                  class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-foreground mb-2">Last Name</label>
                <input
                  v-model="profileForm.lastName"
                  type="text"
                  class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Email</label>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Phone</label>
              <input
                v-model="profileForm.phone"
                type="tel"
                class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-foreground mb-2">Bio</label>
              <textarea
                v-model="profileForm.bio"
                rows="4"
                class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-none"
                placeholder="Tell us about yourself..."
              />
            </div>

            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="resetProfileForm"
                class="px-4 py-2.5 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="profileUpdating"
                class="px-4 py-2.5 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span v-if="profileUpdating" class="flex items-center gap-2">
                  <div class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
                  Saving...
                </span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Security Settings -->
        <div v-if="activeSection === 'security'" class="bg-card border border-border rounded-xl p-6">
          <h2 class="text-lg font-semibold text-foreground mb-6">Security Settings</h2>
          
          <div class="space-y-6">
            <!-- Change Password -->
            <div>
              <h3 class="text-base font-medium text-foreground mb-4">Change Password</h3>
              <form @submit.prevent="changePassword" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-foreground mb-2">Current Password</label>
                  <input
                    v-model="passwordForm.current"
                    type="password"
                    class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-foreground mb-2">New Password</label>
                  <input
                    v-model="passwordForm.new"
                    type="password"
                    class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-foreground mb-2">Confirm New Password</label>
                  <input
                    v-model="passwordForm.confirm"
                    type="password"
                    class="w-full px-3 py-2.5 bg-muted border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                  />
                </div>

                <button
                  type="submit"
                  :disabled="passwordUpdating"
                  class="px-4 py-2.5 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span v-if="passwordUpdating" class="flex items-center gap-2">
                    <div class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
                    Updating...
                  </span>
                  <span v-else>Update Password</span>
                </button>
              </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="border-t border-border pt-6">
              <h3 class="text-base font-medium text-foreground mb-4">Two-Factor Authentication</h3>
              <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50">
                <div>
                  <p class="text-sm font-medium text-foreground">Enable 2FA</p>
                  <p class="text-xs text-muted-foreground">Add an extra layer of security to your account</p>
                </div>
                <button
                  @click="toggle2FA"
                  :class="[
                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                    twoFactorEnabled
                      ? 'bg-danger text-danger-foreground hover:bg-danger/90'
                      : 'bg-primary text-primary-foreground hover:bg-primary/90'
                  ]"
                >
                  {{ twoFactorEnabled ? 'Disable' : 'Enable' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Appearance Settings -->
        <div v-if="activeSection === 'appearance'" class="bg-card border border-border rounded-xl p-6">
          <h2 class="text-lg font-semibold text-foreground mb-6">Appearance</h2>
          
          <div class="space-y-6">
            <!-- Theme -->
            <div>
              <h3 class="text-base font-medium text-foreground mb-4">Theme</h3>
              <div class="grid grid-cols-2 gap-4">
                <button
                  @click="setTheme('light')"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    !themeStore.isDark
                      ? 'border-primary bg-primary/10'
                      : 'border-border hover:border-muted-foreground'
                  ]"
                >
                  <div class="text-center">
                    <AppIcon name="sun" :size="24" class="mx-auto mb-2" />
                    <p class="text-sm font-medium">Light</p>
                  </div>
                </button>
                
                <button
                  @click="setTheme('dark')"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    themeStore.isDark
                      ? 'border-primary bg-primary/10'
                      : 'border-border hover:border-muted-foreground'
                  ]"
                >
                  <div class="text-center">
                    <AppIcon name="moon" :size="24" class="mx-auto mb-2" />
                    <p class="text-sm font-medium">Dark</p>
                  </div>
                </button>
              </div>
            </div>

            <!-- Sidebar -->
            <div>
              <h3 class="text-base font-medium text-foreground mb-4">Sidebar</h3>
              <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50">
                <div>
                  <p class="text-sm font-medium text-foreground">Collapsed by default</p>
                  <p class="text-xs text-muted-foreground">Keep sidebar collapsed on page load</p>
                </div>
                <button
                  @click="themeStore.toggleSidebar"
                  :class="[
                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                    themeStore.sidebarCollapsed ? 'bg-primary' : 'bg-muted'
                  ]"
                >
                  <span
                    :class="[
                      'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                      themeStore.sidebarCollapsed ? 'translate-x-6' : 'translate-x-1'
                    ]"
                  />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- System Setup -->
        <div v-if="activeSection === 'system_setup'" class="bg-card border border-border rounded-xl p-6">
          <h2 class="text-lg font-semibold text-foreground mb-6">System Setup</h2>

          <div v-if="setupLoading" class="text-sm text-muted-foreground">Loading...</div>
          <div v-else class="space-y-8">
            <div v-for="group in setupGroups" :key="group.type">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-foreground">{{ group.label }}</h3>
                <button @click="openAddSetup(group.type)"
                  class="text-xs px-3 py-1 bg-primary text-primary-foreground rounded-full hover:bg-primary/90 transition-colors">
                  + Add
                </button>
              </div>
              <div class="flex flex-wrap gap-2">
                <div v-for="item in setupData[group.type]" :key="item.id"
                  class="flex items-center gap-1.5 px-3 py-1.5 bg-muted rounded-full text-sm">
                  <span v-if="editSetup?.id !== item.id">{{ item.value }}</span>
                  <input v-else v-model="editSetup.value" @keyup.enter="saveEditSetup" @keyup.escape="editSetup = null"
                    class="bg-transparent outline-none w-24 text-sm" />
                  <button v-if="editSetup?.id !== item.id" @click="editSetup = { id: item.id, value: item.value, type: group.type }"
                    class="text-muted-foreground hover:text-foreground">✎</button>
                  <button v-else @click="saveEditSetup" class="text-primary font-bold">✓</button>
                  <button @click="deleteSetupItem(item, group.type)" class="text-muted-foreground hover:text-destructive">×</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Add modal -->
          <div v-if="addSetup.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-card border border-border rounded-xl p-6 w-80 space-y-4">
              <p class="font-semibold text-foreground">Add {{ setupGroups.find(g => g.type === addSetup.type)?.label }}</p>
              <input v-model="addSetup.value" @keyup.enter="saveAddSetup" placeholder="Enter value"
                class="w-full px-3 py-2 bg-muted border border-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
              <div class="flex justify-end gap-2">
                <button @click="addSetup.show = false" class="px-4 py-2 text-sm border border-border rounded-full">Cancel</button>
                <button @click="saveAddSetup" class="px-4 py-2 text-sm bg-primary text-primary-foreground rounded-full">Add</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications Settings -->
        <div v-if="activeSection === 'notifications'" class="bg-card border border-border rounded-xl p-6">
          <h2 class="text-lg font-semibold text-foreground mb-6">Notifications</h2>
          
          <div class="space-y-4">
            <div
              v-for="setting in notificationSettings"
              :key="setting.id"
              class="flex items-center justify-between p-4 rounded-lg bg-muted/50"
            >
              <div>
                <p class="text-sm font-medium text-foreground">{{ setting.title }}</p>
                <p class="text-xs text-muted-foreground">{{ setting.description }}</p>
              </div>
              <button
                @click="toggleNotification(setting.id)"
                :class="[
                  'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                  setting.enabled ? 'bg-primary' : 'bg-muted'
                ]"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    setting.enabled ? 'translate-x-6' : 'translate-x-1'
                  ]"
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';
import { useAuthStore } from '@/stores/auth';
import { useNotificationsStore } from '@/stores/notifications';
import { useResourcesStore } from '@/stores/resources';
import AppIcon from '@/components/ui/AppIcon.vue';

const themeStore = useThemeStore();
const authStore = useAuthStore();
const notificationsStore = useNotificationsStore();
const resourcesStore = useResourcesStore();

const activeSection = ref('profile');
const profileUpdating = ref(false);
const passwordUpdating = ref(false);
const twoFactorEnabled = ref(false);

const settingsSections = [
  { id: 'profile', label: 'Profile', icon: 'user' },
  { id: 'security', label: 'Security', icon: 'shield-check' },
  { id: 'appearance', label: 'Appearance', icon: 'settings-2' },
  { id: 'notifications', label: 'Notifications', icon: 'bell' },
  { id: 'system_setup', label: 'System Setup', icon: 'list' },
];

const profileForm = reactive({
  firstName: authStore.currentUser?.name?.split(' ')[0] || 'Marcus',
  lastName: authStore.currentUser?.name?.split(' ')[1] || 'Webb',
  email: authStore.currentUser?.email || 'marcus@hisaab.com',
  phone: '+1 (555) 123-4567',
  bio: 'Super Admin with extensive experience in franchise management.'
});

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
});

const notificationSettings = ref([
  {
    id: 'email_notifications',
    title: 'Email Notifications',
    description: 'Receive email updates about your account activity',
    enabled: true
  },
  {
    id: 'push_notifications',
    title: 'Push Notifications',
    description: 'Receive browser push notifications for important updates',
    enabled: true
  },
  {
    id: 'marketing_emails',
    title: 'Marketing Emails',
    description: 'Receive emails about new features and updates',
    enabled: false
  },
  {
    id: 'security_alerts',
    title: 'Security Alerts',
    description: 'Get notified about security-related activities',
    enabled: true
  }
]);

const updateProfile = async () => {
  profileUpdating.value = true;
  
  try {
    await authStore.updateProfile({
      name: `${profileForm.firstName} ${profileForm.lastName}`,
      email: profileForm.email,
      phone: profileForm.phone,
      bio: profileForm.bio
    });
    
    notificationsStore.showSuccess('Profile Updated', 'Your profile has been updated successfully');
  } catch (error) {
    console.error('Profile update failed:', error);
  } finally {
    profileUpdating.value = false;
  }
};

const changePassword = async () => {
  if (passwordForm.new !== passwordForm.confirm) {
    notificationsStore.showError('Password Mismatch', 'New passwords do not match');
    return;
  }
  
  passwordUpdating.value = true;
  
  try {
    // Implement password change
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    notificationsStore.showSuccess('Password Changed', 'Your password has been updated successfully');
    
    // Reset form
    passwordForm.current = '';
    passwordForm.new = '';
    passwordForm.confirm = '';
  } catch (error) {
    console.error('Password change failed:', error);
  } finally {
    passwordUpdating.value = false;
  }
};

const setTheme = (theme) => {
  themeStore.setTheme(theme);
  notificationsStore.showSuccess('Theme Changed', `Switched to ${theme} mode`);
};

const toggle2FA = () => {
  twoFactorEnabled.value = !twoFactorEnabled.value;
  const status = twoFactorEnabled.value ? 'enabled' : 'disabled';
  notificationsStore.showInfo('2FA Updated', `Two-factor authentication has been ${status}`);
};

const toggleNotification = (settingId) => {
  const setting = notificationSettings.value.find(s => s.id === settingId);
  if (setting) {
    setting.enabled = !setting.enabled;
    notificationsStore.showInfo('Notification Settings', `${setting.title} has been ${setting.enabled ? 'enabled' : 'disabled'}`);
  }
};

// System Setup
const emptySetupData = { units: [], tax_categories: [], categories: [], stock_categories: [] };
const setupLoading = computed(() => resourcesStore.systemSettingsLoading);
const setupData = computed(() => resourcesStore.systemSettings || emptySetupData);
const editSetup = ref(null);
const addSetup = reactive({ show: false, type: '', value: '' });

const setupGroups = [
  { type: 'units', label: 'Units' },
  { type: 'tax_categories', label: 'Tax Categories' },
  { type: 'categories', label: 'Item Categories' },
  { type: 'stock_categories', label: 'Stock Categories' },
];

const fetchSetup = async () => {
  await resourcesStore.fetchSystemSettings();
};

const openAddSetup = (type) => { addSetup.type = type; addSetup.value = ''; addSetup.show = true; };

const saveAddSetup = async () => {
  if (!addSetup.value.trim()) return;
  await resourcesStore.createSystemSetting({ type: addSetup.type, value: addSetup.value.trim() });
  addSetup.show = false;
};

const saveEditSetup = async () => {
  if (!editSetup.value?.value.trim()) return;
  await resourcesStore.updateSystemSetting(editSetup.value.id, { value: editSetup.value.value.trim() });
  editSetup.value = null;
};

const deleteSetupItem = async (item, type) => {
  if (!confirm(`Delete "${item.value}"?`)) return;
  await resourcesStore.deleteSystemSetting({ ...item, type });
};

watch(activeSection, (val) => { if (val === 'system_setup') fetchSetup(); });

const resetProfileForm = () => {
  profileForm.firstName = authStore.currentUser?.name?.split(' ')[0] || 'Marcus';
  profileForm.lastName = authStore.currentUser?.name?.split(' ')[1] || 'Webb';
  profileForm.email = authStore.currentUser?.email || 'marcus@hisaab.com';
  profileForm.phone = '+1 (555) 123-4567';
  profileForm.bio = 'Super Admin with extensive experience in franchise management.';
};
</script>
