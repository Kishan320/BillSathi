import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from '@/services/api';

const normalize = (d) => (Array.isArray(d) ? d : Array.isArray(d?.data) ? d.data : []);

const buildMeta = (data, page, perPage) => {
  const total = data.total || data.meta?.total || 0;
  return {
    current_page: page,
    last_page: Math.max(1, Math.ceil(total / perPage)),
    from: total ? (page - 1) * perPage + 1 : 0,
    to: Math.min(page * perPage, total),
    total,
  };
};

export const useContactsStore = defineStore('contacts', () => {
  const list = ref([]);
  const meta = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });
  const loading = ref(false);
  const error = ref(null);

  // Dropdown
  const dropdownList = ref([]);
  let dropdownRequest = null;

  // Filter/Search state
  const filters = ref({
    search: '',
    type: null,
    sort: 'name',
    direction: 'asc',
    page: 1,
    perPage: 10,
  });

  // Computed
  const hasError = computed(() => !!error.value);
  const isEmpty = computed(() => list.value.length === 0);
  const isLoading = computed(() => loading.value);

  // Clear error
  const clearError = () => {
    error.value = null;
  };

  // Fetch list with filters
  const fetchList = async (params = {}) => {
    loading.value = true;
    error.value = null;
    try {
      const queryParams = {
        page: params.page || filters.value.page,
        per_page: params.perPage || filters.value.perPage,
        search: params.search || filters.value.search,
        type: params.type || filters.value.type,
        sort: params.sort || filters.value.sort,
        direction: params.direction || filters.value.direction,
      };

      const { data } = await api.get('/contacts', { params: queryParams });
      list.value = normalize(data.data ?? data);
      meta.value = buildMeta(data, queryParams.page, queryParams.perPage);

      // Update filters state
      Object.assign(filters.value, queryParams);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch contacts';
      console.error('Fetch contacts error:', err);
    } finally {
      loading.value = false;
    }
  };

  // Fetch with debounce for search
  let searchTimeout;
  const searchContacts = async (search) => {
    clearTimeout(searchTimeout);
    filters.value.search = search;
    filters.value.page = 1;

    searchTimeout = setTimeout(() => {
      fetchList({ search, page: 1 });
    }, 300);
  };

  // Filter by type
  const filterByType = async (type) => {
    filters.value.type = type;
    filters.value.page = 1;
    await fetchList({ type, page: 1 });
  };

  // Pagination
  const goToPage = async (page) => {
    filters.value.page = page;
    await fetchList({ page });
  };

  const nextPage = async () => {
    if (meta.value.current_page < meta.value.last_page) {
      await goToPage(meta.value.current_page + 1);
    }
  };

  const prevPage = async () => {
    if (meta.value.current_page > 1) {
      await goToPage(meta.value.current_page - 1);
    }
  };

  // Dropdown (for selects in invoices etc.)
  const fetchDropdown = async ({ force = false, type = null } = {}) => {
    if (dropdownList.value.length && !force && !type) return dropdownList.value;
    if (dropdownRequest) return dropdownRequest;

    dropdownRequest = api
      .get('/contacts/dropdown/list', { params: { type, limit: 500 } })
      .then(({ data }) => {
        dropdownList.value = normalize(data.data ?? data);
        return dropdownList.value;
      })
      .catch((err) => {
        console.error('Dropdown fetch error:', err);
        return [];
      })
      .finally(() => {
        dropdownRequest = null;
      });

    return dropdownRequest;
  };

  // Get contacts by type
  const fetchByType = async (type) => {
    try {
      loading.value = true;
      error.value = null;
      const { data } = await api.get(`/contacts/type/${type}`);
      return normalize(data.data ?? data);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch contacts by type';
      console.error('Fetch by type error:', err);
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Upsert (update or insert)
  const upsert = (contact) => {
    const idx = list.value.findIndex((c) => c.id === contact.id);
    if (idx !== -1) {
      list.value[idx] = contact;
    } else {
      list.value.unshift(contact);
    }

    const dIdx = dropdownList.value.findIndex((c) => c.id === contact.id);
    if (dIdx !== -1) {
      dropdownList.value[dIdx] = contact;
    } else {
      dropdownList.value.push(contact);
    }
  };

  // Get single contact by ID
  const getById = (id) => {
    return list.value.find((c) => c.id === id);
  };

  // Create contact
  const createContact = async (payload) => {
    try {
      loading.value = true;
      error.value = null;
      const { data } = await api.post('/contacts', payload);
      upsert(data);
      meta.value.total += 1;
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create contact';
      console.error('Create error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Update contact
  const updateContact = async (id, payload) => {
    try {
      loading.value = true;
      error.value = null;
      const { data } = await api.put(`/contacts/${id}`, payload);
      upsert(data);
      return data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update contact';
      console.error('Update error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Delete contact
  const deleteContact = async (id) => {
    try {
      loading.value = true;
      error.value = null;
      await api.delete(`/contacts/${id}`);
      list.value = list.value.filter((c) => c.id !== id);
      dropdownList.value = dropdownList.value.filter((c) => c.id !== id);
      meta.value.total = Math.max(0, meta.value.total - 1);
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete contact';
      console.error('Delete error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Bulk delete
  const bulkDelete = async (ids) => {
    try {
      loading.value = true;
      error.value = null;
      for (const id of ids) {
        await deleteContact(id);
      }
    } catch (err) {
      error.value = 'Failed to delete some contacts';
      console.error('Bulk delete error:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  };

  // Clear all
  const clearContacts = () => {
    list.value = [];
    dropdownList.value = [];
    dropdownRequest = null;
    meta.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
    filters.value = {
      search: '',
      type: null,
      sort: 'name',
      direction: 'asc',
      page: 1,
      perPage: 10,
    };
    error.value = null;
  };

  // Reset filters
  const resetFilters = async () => {
    filters.value = {
      search: '',
      type: null,
      sort: 'name',
      direction: 'asc',
      page: 1,
      perPage: 10,
    };
    await fetchList();
  };

  return {
    // State
    list,
    meta,
    loading,
    error,
    filters,
    dropdownList,

    // Computed
    hasError,
    isEmpty,
    isLoading,

    // Methods - Main CRUD
    fetchList,
    createContact,
    updateContact,
    deleteContact,

    // Methods - Dropdown
    fetchDropdown,
    fetchByType,

    // Methods - Utilities
    getById,
    upsert,
    clearError,
    clearContacts,
    resetFilters,

    // Methods - Search & Filter
    searchContacts,
    filterByType,

    // Methods - Pagination
    goToPage,
    nextPage,
    prevPage,

    // Methods - Bulk
    bulkDelete,
  };
});
