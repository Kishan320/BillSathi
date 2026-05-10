import { ref, reactive, computed, watch } from 'vue';

export function useForm(initialData = {}, validationRules = {}) {
  const form = reactive({ ...initialData });
  const errors = ref({});
  const touched = ref({});
  const isSubmitting = ref(false);
  const isValid = computed(() => {
    return Object.keys(errors.value).length === 0;
  });

  const validate = () => {
    const newErrors = {};
    
    Object.keys(validationRules).forEach(field => {
      const rules = validationRules[field];
      const value = form[field];
      
      if (rules.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
        newErrors[field] = rules.required || `${field} is required`;
        return;
      }
      
      if (rules.min && value && value.length < rules.min) {
        newErrors[field] = rules.minMessage || `${field} must be at least ${rules.min} characters`;
        return;
      }
      
      if (rules.max && value && value.length > rules.max) {
        newErrors[field] = rules.maxMessage || `${field} must not exceed ${rules.max} characters`;
        return;
      }
      
      if (rules.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        newErrors[field] = rules.emailMessage || `${field} must be a valid email address`;
        return;
      }
      
      if (rules.pattern && value && !rules.pattern.test(value)) {
        newErrors[field] = rules.patternMessage || `${field} format is invalid`;
        return;
      }
      
      if (rules.custom && typeof rules.custom === 'function') {
        const customError = rules.custom(value, form);
        if (customError) {
          newErrors[field] = customError;
        }
      }
    });
    
    errors.value = newErrors;
    return Object.keys(newErrors).length === 0;
  };

  const setField = (field, value) => {
    form[field] = value;
    touched.value[field] = true;
    
    if (errors.value[field]) {
      validate();
    }
  };

  const setError = (field, message) => {
    errors.value[field] = message;
  };

  const clearErrors = () => {
    errors.value = {};
  };

  const clearField = (field) => {
    form[field] = '';
    delete errors.value[field];
    delete touched.value[field];
  };

  const reset = () => {
    Object.assign(form, initialData);
    errors.value = {};
    touched.value = {};
    isSubmitting.value = false;
  };

  const submit = async (callback) => {
    if (!validate()) {
      return false;
    }
    
    isSubmitting.value = true;
    clearErrors();
    
    try {
      const result = await callback(form);
      return result;
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors;
      }
      throw error;
    } finally {
      isSubmitting.value = false;
    }
  };

  // Auto-validate on field change
  watch(form, (newForm, oldForm) => {
    Object.keys(newForm).forEach(field => {
      if (newForm[field] !== oldForm?.[field] && touched.value[field]) {
        validate();
      }
    });
  }, { deep: true });

  return reactive({
    form,
    errors: computed(() => errors.value),
    touched: computed(() => touched.value),
    isSubmitting: computed(() => isSubmitting.value),
    isValid,
    validate,
    setField,
    setError,
    clearErrors,
    clearField,
    reset,
    submit
  });
}

export function useSearchForm(initialQuery = '', debounceMs = 300) {
  const query = ref(initialQuery);
  const debouncedQuery = ref(initialQuery);
  let timeoutId = null;

  const search = (searchTerm) => {
    query.value = searchTerm;
    
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      debouncedQuery.value = searchTerm;
    }, debounceMs);
  };

  const clearSearch = () => {
    search('');
  };

  const reset = () => {
    search(initialQuery);
  };

  return {
    query: computed(() => query.value),
    debouncedQuery: computed(() => debouncedQuery.value),
    search,
    clearSearch,
    reset
  };
}
