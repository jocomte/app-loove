// frontend/src/config.js
// Configuration dynamique de l'URL API pour l'application

const FALLBACK_API_HOST = "10.45.31.160";
const API_PATH = "/app-loove/backend/index.php";

export const getApiUrl = () => {
  const protocol = window.location.protocol;
  const host = window.location.hostname || FALLBACK_API_HOST;
  const port = window.location.port ? `:${window.location.port}` : "";
  return `${protocol}//${host}${port}${API_PATH}`;
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

export const fetchJson = async (url, options = {}, timeoutMs = 15000) => {
  const response = await fetchWithTimeout(url, options, timeoutMs);
  const result = await response.json();

  if (!response.ok) {
    const message = result?.error || `Erreur ${response.status}`;
    throw new Error(message);
  }

  return result;
};
