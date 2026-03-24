/**
 * REST API helper for WP endpoints.
 */

import { getRestUrl, getRestNonce } from './firebase';

export async function apiFetch<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const url = getRestUrl().replace(/\/$/, '') + endpoint;
  const resp = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': getRestNonce(),
      ...options.headers,
    },
  });
  const data = await resp.json();
  if (!resp.ok) throw new Error(data.message || 'API Error');
  return data;
}
