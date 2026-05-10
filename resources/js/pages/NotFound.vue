<template>
  <div class="min-h-screen flex items-center justify-center bg-background px-4">
    <div class="text-center max-w-md">
      <div class="mb-8">
        <h1 class="text-6xl font-bold text-foreground mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-foreground mb-2">Page Not Found</h2>
        <p class="text-muted-foreground mb-8">
          The page you're looking for doesn't exist or has been moved.
        </p>
      </div>
      
      <div class="space-y-4">
        <button
          @click="goHome"
          class="w-full px-6 py-3 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 transition-colors"
        >
          Go to Dashboard
        </button>
        
        <button
          @click="goBack"
          class="w-full px-6 py-3 bg-card border border-border text-foreground font-medium rounded-lg hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/30 transition-colors"
        >
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goHome = () => {
  router.push('/dashboard');
};

const goBack = () => {
  router.go(-1);
};
</script>
