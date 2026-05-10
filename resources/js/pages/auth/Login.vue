<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="max-w-md w-full">
      <!-- Logo and Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <AppLogo :size="48" />
        </div>
        <h1 class="text-2xl font-bold text-foreground mb-2">Welcome back</h1>
        <p class="text-sm text-muted-foreground">Sign in to your Hisaab account</p>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-foreground mb-2">
              Email address
            </label>
            <div class="relative">
              <AppIcon
                name="mail"
                :size="16"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
              />
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                :class="[
                  'w-full pl-10 pr-3 py-2.5 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors',
                  form.errors.email ? 'border-danger' : ''
                ]"
                placeholder="Enter your email"
                :disabled="form.isSubmitting"
              />
            </div>
            <p v-if="form.errors.email" class="mt-1 text-xs text-danger">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-foreground mb-2">
              Password
            </label>
            <div class="relative">
              <AppIcon
                name="lock"
                :size="16"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
              />
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                :class="[
                  'w-full pl-10 pr-10 py-2.5 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors',
                  form.errors.password ? 'border-danger' : ''
                ]"
                placeholder="Enter your password"
                :disabled="form.isSubmitting"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
              >
                <AppIcon :name="showPassword ? 'eye-off' : 'eye'" :size="16" />
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-1 text-xs text-danger">
              {{ form.errors.password }}
            </p>
          </div>
        </div>

        <!-- Remember me & Forgot password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-foreground cursor-pointer">
            <input
              v-model="form.remember"
              type="checkbox"
              class="w-4 h-4 text-primary bg-card border-border rounded focus:ring-primary focus:ring-2"
            />
            Remember me
          </label>
          <button
            type="button"
            @click="goToForgotPassword"
            class="text-sm text-primary hover:underline"
          >
            Forgot password?
          </button>
        </div>

        <!-- Error message -->
        <div
          v-if="authStore?.error"
          class="p-3 rounded-lg bg-danger-bg text-danger text-sm"
        >
          {{ authStore?.error }}
        </div>

        <button
          type="submit"
          :disabled="form.isSubmitting"
          class="w-full py-2.5 px-4 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="form.isSubmitting" class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
            Signing in...
          </span>
          <span v-else>Sign in</span>
        </button>
      </form>

      <!-- Sign up link -->
      <div class="mt-8 text-center">
        <p class="text-sm text-muted-foreground">
          Don't have an account?
          <button
            @click="goToRegister"
            class="text-primary hover:underline font-medium"
          >
            Sign up
          </button>
        </p>
      </div>

      <!-- Demo credentials -->
      <div class="mt-6 p-4 rounded-lg bg-muted/50 border border-border">
        <p class="text-xs text-muted-foreground mb-2">Demo credentials:</p>
        <p class="text-xs font-mono text-foreground">Email: admin@hisaab.com</p>
        <p class="text-xs font-mono text-foreground">Password: password</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useForm } from '@/composables/useForm';
import { useAuth } from '@/composables/useAuth';
import AppLogo from '@/components/ui/AppLogo.vue';
import AppIcon from '@/components/ui/AppIcon.vue';

const router = useRouter();
const { login } = useAuth();

const showPassword = ref(false);

const validationRules = {
  email: {
    required: 'Email is required',
    email: 'Please enter a valid email address'
  },
  password: {
    required: 'Password is required',
    min: 6,
    minMessage: 'Password must be at least 6 characters'
  }
};

const form = useForm(
  {
    email: 'admin@hisaab.com',
    password: 'password',
    remember: false
  },
  validationRules
);

const handleLogin = async () => {
  await form.submit(async (formData) => {
    await login({
      email: formData.email,
      password: formData.password,
      remember: formData.remember
    });
  });
};

const goToRegister = () => {
  router.push('/register');
};

const goToForgotPassword = () => {
  // Implement forgot password functionality
  console.log('Forgot password clicked');
};
</script>
