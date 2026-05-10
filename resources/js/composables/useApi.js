import { ref, computed } from 'vue';
import { api } from '@/services/api';

export function useApi() {
  const loading = ref(false);
  const error = ref(null);
  const data = ref(null);

  const execute = async (apiCall, ...args) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await apiCall(...args);
      data.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const reset = () => {
    loading.value = false;
    error.value = null;
    data.value = null;
  };

  return {
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    data: computed(() => data.value),
    execute,
    reset
  };
}

export function usePaginatedApi(apiCall, defaultParams = {}) {
  const loading = ref(false);
  const error = ref(null);
  const data = ref([]);
  const meta = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  });
  const params = ref({ ...defaultParams });

  const fetch = async (page = 1) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await apiCall({
        ...params.value,
        page
      });
      
      data.value = response.data;
      meta.value = response.meta || response.pagination || meta.value;
      
      return response;
    } catch (err) {
      error.value = err;
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const nextPage = () => {
    if (meta.value.current_page < meta.value.last_page) {
      fetch(meta.value.current_page + 1);
    }
  };

  const prevPage = () => {
    if (meta.value.current_page > 1) {
      fetch(meta.value.current_page - 1);
    }
  };

  const goToPage = (page) => {
    if (page >= 1 && page <= meta.value.last_page) {
      fetch(page);
    }
  };

  const refresh = () => {
    fetch(meta.value.current_page);
  };

  const updateParams = (newParams) => {
    params.value = { ...params.value, ...newParams };
    fetch(1);
  };

  const reset = () => {
    loading.value = false;
    error.value = null;
    data.value = [];
    meta.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    };
    params.value = { ...defaultParams };
  };

  return {
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    data: computed(() => data.value),
    meta: computed(() => meta.value),
    params: computed(() => params.value),
    fetch,
    nextPage,
    prevPage,
    goToPage,
    refresh,
    updateParams,
    reset
  };
}
