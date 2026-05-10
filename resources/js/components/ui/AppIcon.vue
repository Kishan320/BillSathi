<template>
  <component
    :is="iconComponent"
    :size="size"
    :class="iconClass"
    v-bind="$attrs"
  />
</template>

<script setup>
import { computed } from 'vue';

// Icon imports
import {
  LayoutDashboard,
  Building2,
  MapPin,
  TrendingUp,
  Package,
  Truck,
  Users,
  UserCog,
  CreditCard,
  Megaphone,
  BarChart3,
  Settings2,
  Sparkles,
  Bell,
  MessageSquare,
  ClipboardList,
  ShieldCheck,
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  X,
  LogOut,
  Menu,
  Search,
  HelpCircle,
  Command,
  Check,
  AlertTriangle,
  Info,
  XCircle,
  Plus,
  Edit,
  Trash2,
  Eye,
  EyeOff,
  Download,
  Upload,
  Filter,
  RefreshCw,
  MoreVertical,
  Calendar,
  Clock,
  User,
  Mail,
  Phone,
  Map,
  FileText,
  Home,
  ArrowLeft,
  ArrowRight,
  ArrowUp,
  ArrowDown
} from 'lucide-vue-next';

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: [Number, String],
    default: 20
  },
  class: {
    type: String,
    default: ''
  }
});

const iconMap = {
  'layout-dashboard': LayoutDashboard,
  'building-2': Building2,
  'map-pin': MapPin,
  'trending-up': TrendingUp,
  'package': Package,
  'truck': Truck,
  'users': Users,
  'user-cog': UserCog,
  'credit-card': CreditCard,
  'megaphone': Megaphone,
  'bar-chart-3': BarChart3,
  'settings-2': Settings2,
  'sparkles': Sparkles,
  'bell': Bell,
  'message-square': MessageSquare,
  'clipboard-list': ClipboardList,
  'shield-check': ShieldCheck,
  'chevron-left': ChevronLeft,
  'chevron-right': ChevronRight,
  'chevron-down': ChevronDown,
  'x': X,
  'log-out': LogOut,
  'menu': Menu,
  'search': Search,
  'help-circle': HelpCircle,
  'command': Command,
  'check': Check,
  'alert-triangle': AlertTriangle,
  'info': Info,
  'x-circle': XCircle,
  'plus': Plus,
  'edit': Edit,
  'trash-2': Trash2,
  'eye': Eye,
  'eye-off': EyeOff,
  'download': Download,
  'upload': Upload,
  'filter': Filter,
  'refresh-cw': RefreshCw,
  'more-vertical': MoreVertical,
  'calendar': Calendar,
  'clock': Clock,
  'user': User,
  'mail': Mail,
  'phone': Phone,
  'map': Map,
  'file-text': FileText,
  'home': Home,
  'arrow-left': ArrowLeft,
  'arrow-right': ArrowRight,
  'arrow-up': ArrowUp,
  'arrow-down': ArrowDown
};

const iconComponent = computed(() => {
  return iconMap[props.name] || Info;
});

const iconClass = computed(() => {
  return props.class;
});
</script>
