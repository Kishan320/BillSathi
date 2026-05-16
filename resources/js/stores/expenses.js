import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

const normalize = (d) => (Array.isArray(d) ? d : Array.isArray(d?.data) ? d.data : []);

const buildMeta = (data, page, perPage) => {
  const total = data.total || data.meta?.total || 0;
  const resolvedPerPage = data.per_page || data.meta?.per_page || perPage;
  return {
    current_page: page,
    last_page: Math.max(1, Math.ceil(total / resolvedPerPage)),
    from: total ? (page - 1) * resolvedPerPage + 1 : 0,
    to: Math.min(page * resolvedPerPage, total),
    total,
  };
};

export const useExpensesStore = defineStore('expenses', () => {
  const list = ref([]);
  const meta = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });
  const loading = ref(false);
  const error = ref(null);

  const filters = ref({
    search: '',
    status: '',
    page: 1,
    per_page: 10,
  });

  const isEmpty = computed(() => !loading.value && list.value.length === 0);

  const fetchList = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const queryParams = {
        page: params.page ?? filters.value.page,
        per_page: params.per_page ?? filters.value.per_page,
        search: params.search ?? filters.value.search,
        status: params.status ?? filters.value.status,
      };

      const { data } = await api.get('/expenses', { params: queryParams });
      list.value = normalize(data.data ?? data);
      meta.value = buildMeta(data, queryParams.page, queryParams.per_page);
      Object.assign(filters.value, queryParams);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch expenses';
      console.error('Fetch expenses error:', err);
    } finally {
      loading.value = false;
    }
  };

  const upsert = (expense) => {
    const idx = list.value.findIndex((e) => e.id === expense.id);
    if (idx !== -1) list.value[idx] = expense;
    else list.value.unshift(expense);
  };

  const createExpense = async (payload) => {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await api.post('/expenses', payload);
      upsert(data);
      meta.value.total += 1;
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create expense';
      console.error('Create expense error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateExpense = async (id, payload) => {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await api.put(`/expenses/${id}`, payload);
      upsert(data);
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update expense';
      console.error('Update expense error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteExpense = async (id) => {
    loading.value = true;
    error.value = null;
    try {
      await api.delete(`/expenses/${id}`);
      list.value = list.value.filter((e) => e.id !== id);
      meta.value.total = Math.max(0, meta.value.total - 1);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete expense';
      console.error('Delete expense error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  return {
    list,
    meta,
    loading,
    error,
    filters,
    isEmpty,
    fetchList,
    createExpense,
    updateExpense,
    deleteExpense,
  };
});
