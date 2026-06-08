// frontend/src/config.js
// Configuration dynamique de l'URL API pour l'application

export const getApiUrl = () => {
  const protocol = window.location.protocol;
  const host = window.location.hostname;
  const port = window.location.port ? `:${window.location.port}` : "";
  return `${protocol}//${host}${port}/app-loove/backend/index.php`;
};

export const fetchWithTimeout = async (
  url,
  options = {},
  timeoutMs = 15000,
) => {
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

  try {
    const response = await fetch(url, {
      ...options,
      signal: controller.signal,
    });
    clearTimeout(timeoutId);
    return response;
  } catch (error) {
    clearTimeout(timeoutId);
    throw error;
  }
};
