import { defineStore } from 'pinia';
import { markRaw, shallowReactive } from 'vue';

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

const requestKey = (method, url, config = {}) => JSON.stringify({
  method,
  url,
  params: sortObject(config.params || {}),
  responseType: config.responseType || 'json',
});

export const useApiCacheStore = defineStore('api-cache', () => {
  const responses = shallowReactive({});
  const pending = new Map();

  const remember = async (method, url, config, request, { force = false } = {}) => {
    const key = requestKey(method, url, config);

    if (responses[key] && !force) return responses[key];
    if (pending.has(key)) return pending.get(key);

    const promise = request()
      .then((response) => {
        responses[key] = markRaw(response);
        return response;
      })
      .finally(() => {
        pending.delete(key);
      });

    pending.set(key, promise);
    return promise;
  };

  const forget = (matcher = null) => {
    Object.keys(responses).forEach((key) => {
      if (!matcher || matcher(key)) delete responses[key];
    });
  };

  return {
    remember,
    forget,
  };
});
