import { createI18n } from 'vue-i18n';
import en from '@/locales/en.json';
import hi from '@/locales/hi.json';
import gu from '@/locales/gu.json';

const savedLang = localStorage.getItem('lang') || 'en';

const i18n = createI18n({
  legacy: false,
  locale: savedLang,
  fallbackLocale: 'en',
  messages: { en, hi, gu },
});

export default i18n;
