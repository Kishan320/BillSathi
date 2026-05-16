import { defineStore } from 'pinia';
import { ref } from 'vue';
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

export const useVendorsStore = defineStore('vendors', () => {
  // ── Datatable state (Vendors list page) ───────────────────────────────────
  const list = ref([]);
  const meta = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });
  const loading = ref(false);

  const fetchList = async (params = {}) => {
    loading.value = true;
    try {
      const { data } = await api.get('/vendors', { params });
      list.value = normalize(data.data ?? data);
      meta.value = buildMeta(data, params.page || 1, params.per_page || 10);
    } finally {
      loading.value = false;
    }
  };

  // ── Dropdown list (for selects in Purchase / other forms) ─────────────────
  const dropdownList = ref([]);
  let dropdownRequest = null;

  const fetchDropdown = async ({ force = false } = {}) => {
    if (dropdownList.value.length && !force) return dropdownList.value;
    if (dropdownRequest) return dropdownRequest;
    dropdownRequest = api.get('/vendors', { params: { per_page: 500 } })
      .then(({ data }) => {
        dropdownList.value = normalize(data.data ?? data);
        return dropdownList.value;
      })
      .finally(() => { dropdownRequest = null; });
    return dropdownRequest;
  };

  // ── Upsert helper ─────────────────────────────────────────────────────────
  const upsert = (vendor) => {
    // update datatable list
    const idx = list.value.findIndex((v) => v.id === vendor.id);
    if (idx !== -1) list.value[idx] = vendor;
    else list.value.unshift(vendor);

    // update dropdown list
    const dIdx = dropdownList.value.findIndex((v) => v.id === vendor.id);
    if (dIdx !== -1) dropdownList.value[dIdx] = vendor;
    else dropdownList.value.push(vendor);
  };

  // ── CRUD ──────────────────────────────────────────────────────────────────
  const createVendor = async (payload) => {
    const { data } = await api.post('/vendors', payload);
    upsert(data);
    meta.value.total += 1;
    return data;
  };

  const updateVendor = async (id, payload) => {
    const { data } = await api.put(`/vendors/${id}`, payload);
    upsert(data);
    return data;
  };

  const deleteVendor = async (id) => {
    await api.delete(`/vendors/${id}`);
    list.value = list.value.filter((v) => v.id !== id);
    dropdownList.value = dropdownList.value.filter((v) => v.id !== id);
    meta.value.total = Math.max(0, meta.value.total - 1);
  };

  // ── Reset ─────────────────────────────────────────────────────────────────
  const clearVendors = () => {
    list.value = [];
    dropdownList.value = [];
    dropdownRequest = null;
    meta.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
  };

  return {
    list,
    meta,
    loading,
    fetchList,
    dropdownList,
    fetchDropdown,
    createVendor,
    updateVendor,
    deleteVendor,
    clearVendors,
  };
});
