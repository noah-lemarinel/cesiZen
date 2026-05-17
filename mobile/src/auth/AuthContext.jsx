import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import {
  clearStoredSession,
  getStoredToken,
  getStoredUser,
  loginRequest,
  registerRequest,
  setStoredToken,
  setStoredUser,
} from '../lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => getStoredToken());
  const [user, setUser] = useState(() => getStoredUser());
  const [ready, setReady] = useState(false);

  useEffect(() => {
    setReady(true);
  }, []);

  const persistSession = (session) => {
    setToken(session?.token || '');
    setUser(session?.user || null);
    setStoredToken(session?.token || '');
    setStoredUser(session?.user || null);
  };

  const login = async ({ email, password }) => {
    const session = await loginRequest({ email, password });
    persistSession(session);
    return session;
  };

  const register = async ({ name, email, password }) => {
    const session = await registerRequest({ name, email, password });
    persistSession(session);
    return session;
  };

  const logout = () => {
    clearStoredSession();
    setToken('');
    setUser(null);
  };

  const value = useMemo(
    () => ({
      token,
      user,
      ready,
      isAuthenticated: Boolean(token),
      login,
      register,
      logout,
    }),
    [token, user, ready],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}

