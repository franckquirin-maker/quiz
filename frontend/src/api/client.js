const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

async function request(path, options = {}) {
  const response = await fetch(`${API_URL}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  });

  if (response.status === 204) {
    return null;
  }

  const data = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(data?.error || `Erreur ${response.status}`);
  }

  return data;
}

export const api = {
  get: (path) => request(path),
  post: (path, body) => request(path, { method: 'POST', body: JSON.stringify(body) }),
  put: (path, body) => request(path, { method: 'PUT', body: JSON.stringify(body) }),
  del: (path) => request(path, { method: 'DELETE' }),
};

export const MERCURE_URL = import.meta.env.VITE_MERCURE_URL || 'http://localhost:3000/.well-known/mercure';

/**
 * Téléverse un fichier image/vidéo pour une question. Ne pas fixer le
 * Content-Type ici : le navigateur doit poser lui-même le boundary multipart.
 */
export async function uploadMedia(file) {
  const formData = new FormData();
  formData.append('file', file);

  const response = await fetch(`${API_URL}/api/media/upload`, {
    method: 'POST',
    body: formData,
  });

  const data = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(data?.error || `Erreur ${response.status}`);
  }

  return data;
}

/**
 * Résout l'URL d'un média de question. Les chemins relatifs (ex. /media/q1.jpg)
 * sont servis par le backend, pas par le frontend : il faut donc les préfixer
 * avec l'origine de l'API. Les URLs absolues (http/https) sont laissées telles quelles.
 */
export function mediaUrl(path) {
  if (!path) return path;
  if (/^https?:\/\//i.test(path)) return path;
  return `${API_URL}${path.startsWith('/') ? '' : '/'}${path}`;
}
