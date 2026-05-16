import { defineStore } from 'pinia';
import { computed, reactive, ref } from 'vue';
import { api } from '@/services/api';

const sortObject = (value) => {
  if (Array.isArray(value)) return value.map(sortObject);
  if (!value || typeof value !== 'object') return value;

  return Object.keys(value)
    .sort()
    .reduce((acc, key) => {
      acc[key] = sortObject(value[key]);
      return acc;
    }, {});
};

const buildKey = (url, params = {}) => JSON.stringify({ url, params: sortObject(params) });

const normalizeItems = (payload) => payload?.data || payload || [];

const itemMatchesParams = (item, params = {}) => {
  const search = params.search?.trim().toLowerCase();
  const type = params.item_type;

  if (type && item.item_type !== type) return false;
  if (search && !item.name?.toLowerCase().includes(search)) return false;

  return true;
};

const upsertById = (list, item) => {
  const index = list.findIndex((existing) => existing.id === item.id);
  if (index === -1) list.push(item);
  else list[index] = item;
};

export const useResourcesStore = defineStore('resources', () => {
  const systemSettings = ref(null);
  const systemSettingsLoading = ref(false);
  let systemSettingsRequest = null;

  const itemLists = reactive({});
  const itemPayloads = reactive({});
  const itemLoading = reactive({});
  const itemRequests = new Map();

  const setupOptions = computed(() => {
    const defaults = {
      units: ['Pcs'],
      taxCategories: ['GST 0%'],
      categories: ['Other'],
      stockCategories: ['Other'],
    };

    if (!systemSettings.value) return defaults;

    return {
      units: systemSettings.value.units?.map((item) => item.value) || defaults.units,
      taxCategories: systemSettings.value.tax_categories?.map((item) => item.value) || defaults.taxCategories,
      categories: systemSettings.value.categories?.map((item) => item.value) || defaults.categories,
      stockCategories: systemSettings.value.stock_categories?.map((item) => item.value) || defaults.stockCategories,
    };
  });

  const fetchSystemSettings = async ({ force = false } = {}) => {
    if (systemSettings.value && !force) return systemSettings.value;
    if (systemSettingsRequest) return systemSettingsRequest;

    systemSettingsLoading.value = true;
    systemSettingsRequest = api.get('/system-settings')
      .then((response) => {
        systemSettings.value = response.data;
        return systemSettings.value;
      })
      .finally(() => {
        systemSettingsLoading.value = false;
        systemSettingsRequest = null;
      });

    return systemSettingsRequest;
  };

  const createSystemSetting = async (payload) => {
    const response = await api.post('/system-settings', payload);

    if (!systemSettings.value) {
      systemSettings.value = { units: [], tax_categories: [], categories: [], stock_categories: [] };
    }

    if (!systemSettings.value[payload.type]) systemSettings.value[payload.type] = [];
    systemSettings.value[payload.type].push(response.data);

    return response.data;
  };

  const updateSystemSetting = async (id, payload) => {
    const response = await api.put(`/system-settings/${id}`, payload);
    const updated = response.data;

    if (systemSettings.value?.[updated.type]) {
      const index = systemSettings.value[updated.type].findIndex((item) => item.id === updated.id);
      if (index !== -1) systemSettings.value[updated.type][index] = updated;
    }

    return updated;
  };

  const deleteSystemSetting = async (item) => {
    await api.delete(`/system-settings/${item.id}`);

    if (systemSettings.value?.[item.type]) {
      systemSettings.value[item.type] = systemSettings.value[item.type].filter((setting) => setting.id !== item.id);
    }
  };

  const fetchItems = async (params = {}, { force = false } = {}) => {
    const key = buildKey('/items', params);

    if (itemLists[key] && !force) return itemLists[key];
    if (itemRequests.has(key)) return itemRequests.get(key);

    itemLoading[key] = true;
    const request = api.get('/items', { params })
      .then((response) => {
        itemPayloads[key] = response.data;
        itemLists[key] = normalizeItems(response.data);
        return itemLists[key];
      })
      .finally(() => {
        itemLoading[key] = false;
        itemRequests.delete(key);
      });

    itemRequests.set(key, request);
    return request;
  };

  const getItems = (params = {}) => itemLists[buildKey('/items', params)] || [];
  const isItemsLoading = (params = {}) => !!itemLoading[buildKey('/items', params)];

  const syncItemLists = (item, previousItem = null) => {
    Object.keys(itemLists).forEach((key) => {
      const { params } = JSON.parse(key);
      const list = itemLists[key];
      const shouldInclude = itemMatchesParams(item, params);
      const previousIndex = previousItem ? list.findIndex((existing) => existing.id === previousItem.id) : -1;

      if (shouldInclude) {
        upsertById(list, item);
      } else if (previousIndex !== -1) {
        list.splice(previousIndex, 1);
      }
    });
  };

  const createItem = async (payload) => {
    const response = await api.post('/items', payload);
    syncItemLists(response.data);
    return response.data;
  };

  const updateItem = async (id, payload) => {
    const previousItem = Object.values(itemLists)
      .flat()
      .find((item) => item.id === id);
    const response = await api.put(`/items/${id}`, payload);
    syncItemLists(response.data, previousItem);
    return response.data;
  };

  const deleteItem = async (item) => {
    await api.delete(`/items/${item.id}`);

    Object.keys(itemLists).forEach((key) => {
      itemLists[key] = itemLists[key].filter((existing) => existing.id !== item.id);
    });
  };

  // ── Warehouses ────────────────────────────────────────────────────────────
  const warehouses = ref([]);
  let warehousesRequest = null;

  const fetchWarehouses = async ({ force = false } = {}) => {
    if (warehouses.value.length && !force) return warehouses.value;
    if (warehousesRequest) return warehousesRequest;
    warehousesRequest = api.get('/warehouses', { params: { per_page: 100, status: 1 } })
      .then((r) => {
        const d = r.data;
        warehouses.value = Array.isArray(d) ? d : (Array.isArray(d?.data) ? d.data : []);
        return warehouses.value;
      })
      .finally(() => { warehousesRequest = null; });
    return warehousesRequest;
  };

  // ── Purchases ─────────────────────────────────────────────────────────────
  const purchasesList = ref([]);
  const purchasesMeta = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });

  const fetchPurchases = async (params = {}) => {
    const { data } = await api.get('/purchases', { params });
    purchasesList.value = data.data || data;
    const total = data.recordsFiltered || data.total || purchasesList.value.length;
    const page = params.page || Math.floor((params.start || 0) / (params.length || 10)) + 1;
    const perPage = params.length || 10;
    purchasesMeta.value = {
      current_page: page,
      last_page: Math.max(1, Math.ceil(total / perPage)),
      from: total ? (page - 1) * perPage + 1 : 0,
      to: Math.min(page * perPage, total),
      total,
    };
    return purchasesList.value;
  };

  const upsertPurchase = (purchase) => {
    const index = purchasesList.value.findIndex((p) => p.id === purchase.id);
    if (index !== -1) purchasesList.value[index] = purchase;
    else purchasesList.value.unshift(purchase);
  };

  const createPurchase = async (payload) => {
    const { data } = await api.post('/purchases', payload);
    upsertPurchase(data);
    return data;
  };

  const updatePurchase = async (id, payload) => {
    const { data } = await api.put(`/purchases/${id}`, payload);
    upsertPurchase(data);
    return data;
  };

  const deletePurchase = async (id) => {
    await api.delete(`/purchases/${id}`);
    purchasesList.value = purchasesList.value.filter((p) => p.id !== id);
  };

  const getPurchaseById = async (id) => {
    const { data } = await api.get(`/purchases/${id}`);
    return data;
  };

  const clearResources = () => {
    systemSettings.value = null;
    systemSettingsRequest = null;
    Object.keys(itemLists).forEach((key) => delete itemLists[key]);
    Object.keys(itemPayloads).forEach((key) => delete itemPayloads[key]);
    Object.keys(itemLoading).forEach((key) => delete itemLoading[key]);
    itemRequests.clear();
    warehouses.value = [];
    warehousesRequest = null;
    purchasesList.value = [];
  };

  return {
    systemSettings,
    systemSettingsLoading,
    setupOptions,
    fetchSystemSettings,
    createSystemSetting,
    updateSystemSetting,
    deleteSystemSetting,
    fetchItems,
    getItems,
    isItemsLoading,
    createItem,
    updateItem,
    deleteItem,
    warehouses,
    fetchWarehouses,
    purchasesList,
    purchasesMeta,
    fetchPurchases,
    createPurchase,
    updatePurchase,
    deletePurchase,
    getPurchaseById,
    clearResources,
  };
});
