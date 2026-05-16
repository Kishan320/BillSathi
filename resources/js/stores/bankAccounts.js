import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

const normalize = (d) => (Array.isArray(d) ? d : Array.isArray(d?.data) ? d.data : []);

export const useBankAccountsStore = defineStore('bankAccounts', () => {
  const list = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref(null);

  // Search + filters
  const search = ref('');
  const includeClosed = ref(true);

  // Modal state
  const modalOpen = ref(false);
  const editing = ref(null); // account object
  const fieldErrors = ref({});

  // Request de-dupe
  let fetchRequest = null;

  const isEmpty = computed(() => !loading.value && list.value.length === 0);

  const openCreate = () => {
    editing.value = null;
    fieldErrors.value = {};
    modalOpen.value = true;
  };

  const openEdit = (account) => {
    editing.value = account;
    fieldErrors.value = {};
    modalOpen.value = true;
  };

  const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    fieldErrors.value = {};
  };

  const fetchAccounts = async ({ force = false } = {}) => {
    if (fetchRequest && !force) return fetchRequest;

    loading.value = true;
    error.value = null;

    fetchRequest = api
      .get('/bank-accounts', {
        params: {
          search: search.value || undefined,
          include_closed: includeClosed.value ? 1 : 0,
        },
      })
      .then(({ data }) => {
        list.value = normalize(data);
        return list.value;
      })
      .catch((err) => {
        error.value = err.response?.data?.message || 'Failed to fetch bank accounts';
        console.error('Fetch bank accounts error:', err);
        return [];
      })
      .finally(() => {
        loading.value = false;
        fetchRequest = null;
      });

    return fetchRequest;
  };

  let searchTimeout;
  const setSearch = (value) => {
    search.value = value;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchAccounts({ force: true }), 250);
  };

  const setIncludeClosed = async (value) => {
    includeClosed.value = !!value;
    await fetchAccounts({ force: true });
  };

  const upsert = (account) => {
    const idx = list.value.findIndex((a) => a.id === account.id);
    if (idx !== -1) list.value[idx] = account;
    else list.value.unshift(account);
  };

  const createAccount = async (payload) => {
    saving.value = true;
    error.value = null;
    fieldErrors.value = {};
    try {
      const { data } = await api.post('/bank-accounts', payload);
      upsert(data.data ?? data);
      closeModal();
      return data.data ?? data;
    } catch (err) {
      fieldErrors.value = err.response?.data?.errors || {};
      error.value = err.response?.data?.message || 'Failed to create bank account';
      throw err;
    } finally {
      saving.value = false;
    }
  };

  const updateAccount = async (id, payload) => {
    saving.value = true;
    error.value = null;
    fieldErrors.value = {};
    try {
      const { data } = await api.put(`/bank-accounts/${id}`, payload);
      upsert(data.data ?? data);
      closeModal();
      return data.data ?? data;
    } catch (err) {
      fieldErrors.value = err.response?.data?.errors || {};
      error.value = err.response?.data?.message || 'Failed to update bank account';
      throw err;
    } finally {
      saving.value = false;
    }
  };

  const closeAccount = async (account) => {
    saving.value = true;
    error.value = null;
    try {
      const { data } = await api.post(`/bank-accounts/${account.id}/close`);
      upsert(data.data ?? data);
      return data.data ?? data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to close account';
      throw err;
    } finally {
      saving.value = false;
    }
  };

  const deleteAccount = async (account) => {
    saving.value = true;
    error.value = null;
    try {
      await api.delete(`/bank-accounts/${account.id}`);
      list.value = list.value.filter((a) => a.id !== account.id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete account';
      throw err;
    } finally {
      saving.value = false;
    }
  };

  return {
    list,
    loading,
    saving,
    error,
    search,
    includeClosed,
    modalOpen,
    editing,
    fieldErrors,
    isEmpty,
    openCreate,
    openEdit,
    closeModal,
    fetchAccounts,
    setSearch,
    setIncludeClosed,
    createAccount,
    updateAccount,
    closeAccount,
    deleteAccount,
  };
});

