<template>
  <div
    class="bg-card border border-border rounded-xl p-6 card-hover"
    :class="[cardClass, sizeClass]"
  >
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="flex items-center gap-2 mb-2">
          <AppIcon
            v-if="icon"
            :name="icon"
            :size="iconSize"
            :class="iconClass"
          />
          <h3 class="text-sm font-medium text-muted-foreground">{{ title }}</h3>
        </div>
        
        <div class="flex items-baseline gap-2 mb-2">
          <div class="text-hero-stat font-bold text-foreground font-tabular">
            {{ formattedValue }}
          </div>
          <div
            v-if="change !== null"
            :class="changeClass"
            class="flex items-center gap-1 text-xs font-medium"
          >
            <AppIcon
              :name="changeIcon"
              :size="12"
            />
            {{ Math.abs(change) }}%
          </div>
        </div>
        
        <p v-if="description" class="text-xs text-muted-foreground">
          {{ description }}
        </p>
      </div>
      
      <div v-if="trend" class="flex items-center">
        <AppIcon
          :name="trendIcon"
          :size="16"
          :class="trendClass"
        />
      </div>
    </div>
    
    <div v-if="showSparkline && sparklineData" class="mt-4">
      <div class="h-8 flex items-end gap-0.5">
        <div
          v-for="(value, index) in sparklineData"
          :key="index"
          class="flex-1 bg-primary/20 rounded-t-sm"
          :style="{ height: `${(value / Math.max(...sparklineData)) * 100}%` }"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  change: {
    type: Number,
    default: null
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  trend: {
    type: String,
    default: '',
    validator: (value) => ['up', 'down', 'neutral'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  showSparkline: {
    type: Boolean,
    default: false
  },
  sparklineData: {
    type: Array,
    default: () => []
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'success', 'warning', 'danger', 'info'].includes(value)
  }
});

const sizeConfig = {
  sm: {
    padding: 'p-4',
    iconSize: 16,
    titleSize: 'text-xs'
  },
  md: {
    padding: 'p-6',
    iconSize: 20,
    titleSize: 'text-sm'
  },
  lg: {
    padding: 'p-8',
    iconSize: 24,
    titleSize: 'text-base'
  }
};

const currentSize = computed(() => sizeConfig[props.size] || sizeConfig.md);

const sizeClass = computed(() => currentSize.value.padding);

const iconSize = computed(() => currentSize.value.iconSize);

const iconClass = computed(() => {
  const variantClasses = {
    success: 'text-positive',
    warning: 'text-warning',
    danger: 'text-danger',
    info: 'text-info',
    default: 'text-primary'
  };
  return variantClasses[props.variant] || variantClasses.default;
});

const cardClass = computed(() => {
  const variantClasses = {
    success: 'border-positive/20 bg-positive-bg/50',
    warning: 'border-warning/20 bg-warning-bg/50',
    danger: 'border-danger/20 bg-danger-bg/50',
    info: 'border-info/20 bg-info-bg/50',
    default: ''
  };
  return variantClasses[props.variant] || variantClasses.default;
});

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 1
    }).format(props.value);
  }
  return props.value;
});

const changeClass = computed(() => {
  if (props.change === null) return '';
  return props.change >= 0 ? 'text-positive' : 'text-danger';
});

const changeIcon = computed(() => {
  if (props.change === null) return '';
  return props.change >= 0 ? 'trending-up' : 'trending-down';
});

const trendIcon = computed(() => {
  const trendIcons = {
    up: 'trending-up',
    down: 'trending-down',
    neutral: 'minus'
  };
  return trendIcons[props.trend] || trendIcons.neutral;
});

const trendClass = computed(() => {
  const trendClasses = {
    up: 'text-positive',
    down: 'text-danger',
    neutral: 'text-muted-foreground'
  };
  return trendClasses[props.trend] || trendClasses.neutral;
});
</script>
