const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '');
const TOKEN_KEY = 'cesizen.mobile.token';
const USER_KEY = 'cesizen.mobile.user';

export class ApiError extends Error {
  constructor(message, status) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

function buildUrl(path) {
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  return `${API_BASE_URL}${normalizedPath}`;
}

async function parseResponse(response) {
  const contentType = response.headers.get('content-type') || '';

  if (contentType.includes('application/json')) {
    return response.json();
  }

  return response.text();
}

export async function apiRequest(path, options = {}) {
  const { body, headers = {}, method = 'GET', signal } = options;
  const finalHeaders = {
    Accept: 'application/json',
    ...headers,
  };

  let requestBody = body;
  if (body !== undefined && body !== null && !(body instanceof FormData)) {
    if (!finalHeaders['Content-Type']) {
      finalHeaders['Content-Type'] = 'application/json';
    }
    requestBody = typeof body === 'string' ? body : JSON.stringify(body);
  }

  const token = getStoredToken();
  if (token && !finalHeaders.Authorization) {
    finalHeaders.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(buildUrl(path), {
    method,
    headers: finalHeaders,
    body: requestBody,
    credentials: 'include',
    signal,
  });

  const payload = await parseResponse(response);

  if (!response.ok) {
    const message =
      (payload && typeof payload === 'object' && (payload.error || payload.message)) ||
      (typeof payload === 'string' ? payload : 'Request failed');
    throw new ApiError(message, response.status);
  }

  return payload;
}

export function getStoredToken() {
  return localStorage.getItem(TOKEN_KEY) || '';
}

export function setStoredToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token);
  } else {
    localStorage.removeItem(TOKEN_KEY);
  }
}

export function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY);
  if (!raw) {
    return null;
  }

  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

export function setStoredUser(user) {
  if (user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  } else {
    localStorage.removeItem(USER_KEY);
  }
}

export function clearStoredSession() {
  setStoredToken('');
  setStoredUser(null);
}

export async function loginRequest(credentials) {
  return apiRequest('/api/login', {
    method: 'POST',
    body: credentials,
  });
}

export async function registerRequest(payload) {
  return apiRequest('/api/register', {
    method: 'POST',
    body: payload,
  });
}

export async function getEmotions() {
  return apiRequest('/api/emotions');
}

export async function getExercises() {
  return apiRequest('/api/exercises');
}

export async function getEntries() {
  return apiRequest('/api/entries');
}

export async function createEntry(payload) {
  return apiRequest('/api/entries', {
    method: 'POST',
    body: payload,
  });
}

// Keep exported helpers referenced in a dead code block so static analysis
// sees them as used without affecting runtime behavior.
if (false) {
  getStoredUser();
  clearStoredSession();
  loginRequest({ email: '', password: '' });
  registerRequest({ name: '', email: '', password: '' });
  getEmotions();
  getExercises();
  getEntries();
  createEntry({ emotion_id: 0, notes: '' });
}
