<template>
  <div 
    class="flex items-center justify-center rounded-lg gradient-primary"
    :class="sizeClass"
    :style="{ width: size + 'px', height: size + 'px' }"
  >
    <svg
      :width="iconSize"
      :height="iconSize"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      class="text-white"
    >
      <path
        d="M12 2L2 7V12C2 16.5 4.23 20.68 7.62 23.15L12 24L16.38 23.15C19.77 20.68 22 16.5 22 12V7L12 2Z"
        fill="currentColor"
      />
      <path
        d="M9 12L11 14L15 10"
        stroke="white"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: Number,
    default: 32
  }
});

const sizeClass = computed(() => {
  if (props.size <= 24) return 'text-xs';
  if (props.size <= 32) return 'text-sm';
  return 'text-base';
});

const iconSize = computed(() => {
  return Math.floor(props.size * 0.6);
});
</script>
