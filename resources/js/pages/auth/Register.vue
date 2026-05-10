<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="max-w-md w-full">
      <!-- Logo and Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <AppLogo :size="48" />
        </div>
        <h1 class="text-2xl font-bold text-foreground mb-2">Create your account</h1>
        <p class="text-sm text-muted-foreground">Get started with Hisaab today</p>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="handleRegister" class="space-y-6">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-foreground mb-2">
              Full name
            </label>
            <div class="relative">
              <AppIcon
                name="user"
                :size="16"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
              />
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                :class="[
                  'w-full pl-10 pr-3 py-2.5 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors',
                  form.errors.name ? 'border-danger' : ''
                ]"
                placeholder="Enter your full name"
                :disabled="form.isSubmitting"
              />
            </div>
            <p v-if="form.errors.name" class="mt-1 text-xs text-danger">
              {{ form.errors.name }}
            </p>
          </div>

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
                placeholder="Create a password"
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

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-foreground mb-2">
              Confirm password
            </label>
            <div class="relative">
              <AppIcon
                name="lock"
                :size="16"
                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
              />
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                :class="[
                  'w-full pl-10 pr-10 py-2.5 bg-card border border-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors',
                  form.errors.password_confirmation ? 'border-danger' : ''
                ]"
                placeholder="Confirm your password"
                :disabled="form.isSubmitting"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
              >
                <AppIcon :name="showConfirmPassword ? 'eye-off' : 'eye'" :size="16" />
              </button>
            </div>
            <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-danger">
              {{ form.errors.password_confirmation }}
            </p>
          </div>
        </div>

        <!-- Terms and conditions -->
        <div>
          <label class="flex items-start gap-2 text-sm text-foreground cursor-pointer">
            <input
              v-model="form.terms"
              type="checkbox"
              required
              class="w-4 h-4 text-primary bg-card border-border rounded focus:ring-primary focus:ring-2 mt-0.5"
            />
            <span>
              I agree to the
              <button type="button" @click="showTerms" class="text-primary hover:underline">
                Terms of Service
              </button>
              and
              <button type="button" @click="showPrivacy" class="text-primary hover:underline">
                Privacy Policy
              </button>
            </span>
          </label>
        </div>

        <!-- Error message -->
        <div
          v-if="authStore?.error"
          class="p-3 rounded-lg bg-danger-bg text-danger text-sm"
        >
          {{ authStore?.error }}
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          :disabled="form.isSubmitting || !form.isValid"
          class="w-full py-2.5 px-4 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="form.isSubmitting" class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-primary-foreground/30 border-t-primary-foreground rounded-full animate-spin" />
            Creating account...
          </span>
          <span v-else>Create account</span>
        </button>
      </form>

      <!-- Sign in link -->
      <div class="mt-8 text-center">
        <p class="text-sm text-muted-foreground">
          Already have an account?
          <button
            @click="goToLogin"
            class="text-primary hover:underline font-medium"
          >
            Sign in
          </button>
        </p>
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
const { register } = useAuth();

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const validationRules = {
  name: {
    required: 'Full name is required',
    min: 2,
    minMessage: 'Name must be at least 2 characters'
  },
  email: {
    required: 'Email is required',
    email: 'Please enter a valid email address'
  },
  password: {
    required: 'Password is required',
    min: 8,
    minMessage: 'Password must be at least 8 characters'
  },
  password_confirmation: {
    required: 'Please confirm your password',
    custom: (value, form) => {
      if (value !== form.password) {
        return 'Passwords do not match';
      }
      return null;
    }
  },
  terms: {
    required: 'You must agree to the terms and conditions',
    custom: (value) => {
      if (!value) {
        return 'You must agree to the terms and conditions';
      }
      return null;
    }
  }
};

const form = useForm(
  {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false
  },
  validationRules
);

const handleRegister = async () => {
  try {
    await register({
      name: form.form.name,
      email: form.form.email,
      password: form.form.password,
      password_confirmation: form.form.password_confirmation
    });
  } catch (error) {
    // Error is handled by auth composable
    console.error('Registration failed:', error);
  }
};

const goToLogin = () => {
  router.push('/login');
};

const showTerms = () => {
  // Implement terms modal or navigation
  console.log('Show terms of service');
};

const showPrivacy = () => {
  // Implement privacy policy modal or navigation
  console.log('Show privacy policy');
};
</script>
