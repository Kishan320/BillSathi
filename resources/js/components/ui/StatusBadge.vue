<template>
  <span
    :class="badgeClass"
    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
  >
    <span
      v-if="showDot"
      :class="dotClass"
      class="w-1.5 h-1.5 rounded-full"
    />
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'default'
  },
  text: {
    type: String,
    required: true
  },
  showDot: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
});

const statusConfig = {
  active: {
    bg: 'var(--positive-bg)',
    text: 'var(--positive)',
    dot: 'bg-positive'
  },
  suspended: {
    bg: 'var(--danger-bg)',
    text: 'var(--danger)',
    dot: 'bg-danger'
  },
  onboarding: {
    bg: 'var(--info-bg)',
    text: 'var(--info)',
    dot: 'bg-info'
  },
  review: {
    bg: 'var(--warning-bg)',
    text: 'var(--warning)',
    dot: 'bg-warning'
  },
  terminated: {
    bg: '#F8FAFC',
    text: '#94A3B8',
    dot: 'bg-gray-400'
  },
  lead: {
    bg: 'var(--violet-bg)',
    text: 'var(--violet)',
    dot: 'bg-violet'
  },
  success: {
    bg: 'var(--positive-bg)',
    text: 'var(--positive)',
    dot: 'bg-positive'
  },
  error: {
    bg: 'var(--danger-bg)',
    text: 'var(--danger)',
    dot: 'bg-danger'
  },
  warning: {
    bg: 'var(--warning-bg)',
    text: 'var(--warning)',
    dot: 'bg-warning'
  },
  info: {
    bg: 'var(--info-bg)',
    text: 'var(--info)',
    dot: 'bg-info'
  },
  default: {
    bg: 'var(--muted)',
    text: 'var(--muted-foreground)',
    dot: 'bg-muted-foreground'
  }
};

const sizeConfig = {
  sm: 'px-2 py-0.5 text-[10px]',
  md: 'px-2.5 py-1 text-xs',
  lg: 'px-3 py-1.5 text-sm'
};

const config = computed(() => {
  return statusConfig[props.status] || statusConfig.default;
});

const badgeClass = computed(() => {
  return [
    config.value.bg,
    config.value.text,
    sizeConfig[props.size]
  ];
});

const dotClass = computed(() => {
  return config.value.dot;
});
</script>
