import React, { useEffect, useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';

function LogoMark() {
  return (
    <div className="flex items-center gap-3 flex-shrink-0">
      <div className="rounded-full bg-white/20 p-2">
        <svg className="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <circle cx="12" cy="12" r="9" fill="white" fillOpacity="0.15" />
          <path
            d="M8 13c1.2 1 2.8 1 4 0 1.2-1 2-3 2-3s-1.6-1-3-1c-1.4 0-3 1-3 1s-.2 2 .0 3z"
            fill="white"
            fillOpacity="0.9"
          />
        </svg>
      </div>
      <div className="hidden sm:block">
        <h1 className="text-xl font-semibold">CesiZen</h1>
        <p className="text-xs opacity-90">Santé mentale</p>
      </div>
    </div>
  );
}

function ThemeToggleButton() {
  const [isDark, setIsDark] = useState(false);

  useEffect(() => {
    const html = document.documentElement;
    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialDark = stored === 'dark' || (!stored && prefersDark);
    html.classList.toggle('dark', initialDark);
    setIsDark(initialDark);
  }, []);

  const toggleTheme = () => {
    const html = document.documentElement;
    const nextDark = !html.classList.contains('dark');
    html.classList.toggle('dark', nextDark);
    localStorage.setItem('theme', nextDark ? 'dark' : 'light');
    setIsDark(nextDark);
  };

  return (
    <button
      id="theme-toggle"
      type="button"
      className="p-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200"
      aria-label="Basculer le thème"
      aria-pressed={isDark}
      onClick={toggleTheme}
    >
      <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 ${isDark ? 'hidden' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
      </svg>
      <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 ${isDark ? '' : 'hidden'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.63 5.63m12.02 0l-1.42 1.42M7.05 16.95l-1.42 1.42M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
      </svg>
    </button>
  );
}

export function Header() {
  const { user, logout, isAuthenticated } = useAuth();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [userMenuOpen, setUserMenuOpen] = useState(false);

  const displayName = user?.name || user?.email || 'Connecté';
  const menuItems = [
    { to: '/', label: 'Accueil' },
    { to: '/emotion/tracker', label: "Tracker d'Émotions" },
    { to: '/exercises', label: 'Exercices' },
    { to: '/resources', label: 'Ressources' },
  ];

  useEffect(() => {
    const onClick = (event) => {
      const target = event.target;
      const userMenu = document.getElementById('user-dropdown');
      const userTrigger = document.getElementById('user-menu-trigger');
      if (userMenuOpen && userTrigger && userMenu && !userTrigger.contains(target) && !userMenu.contains(target)) {
        setUserMenuOpen(false);
      }
    };

    document.addEventListener('click', onClick);
    return () => document.removeEventListener('click', onClick);
  }, [userMenuOpen]);

  const handleLogout = () => {
    logout();
  };

  return (
    <header className="bg-primary text-white dark:bg-secondary shadow-md sticky top-0 z-50">
      <div className="container mx-auto px-4 py-4 flex items-center justify-between">
        <Link to="/">
          <LogoMark />
        </Link>

        <nav className="hidden md:flex items-center gap-1">
          {menuItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm${isActive ? ' bg-white/10' : ''}`
              }
            >
              {item.label}
            </NavLink>
          ))}
        </nav>

        <div className="flex items-center gap-2">
          <ThemeToggleButton />

          <div className="hidden md:relative md:block">
            {isAuthenticated ? (
              <>
                <button
                  id="user-menu-trigger"
                  type="button"
                  className="flex items-center gap-2 px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                  onClick={(e) => {
                    e.stopPropagation();
                    setUserMenuOpen((current) => !current);
                  }}
                >
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                  </svg>
                  <span>{displayName}</span>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className={`h-4 w-4 transition-transform duration-200${userMenuOpen ? ' rotate-180' : ''}`}
                    id="dropdown-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                  >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                  </svg>
                </button>
                <div
                  id="user-dropdown"
                  className={`${userMenuOpen ? '' : 'hidden'} absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-md shadow-lg z-50`}
                >
                  <Link to="/account" className="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                    Mon Compte
                  </Link>
                  <hr className="border-gray-200 dark:border-gray-700" />
                  <button
                    type="button"
                    onClick={handleLogout}
                    className="w-full text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-red-600 dark:text-red-400"
                  >
                    Se déconnecter
                  </button>
                </div>
              </>
            ) : (
              <>
                <Link
                  to="/login"
                  className="px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                >
                  Se connecter
                </Link>
                <Link
                  to="/register"
                  className="px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                >
                  S'inscrire
                </Link>
              </>
            )}
          </div>

          <button
            id="mobile-menu-trigger"
            type="button"
            className="md:hidden p-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200"
            aria-label="Menu"
            onClick={() => setMobileMenuOpen((current) => !current)}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-6 w-6"
              id="menu-icon"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              style={{ transform: mobileMenuOpen ? 'rotate(180deg)' : 'rotate(0)' }}
            >
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      <div className={`${mobileMenuOpen ? '' : 'hidden'} md:hidden bg-primary dark:bg-secondary border-t border-white/20`}>
        <nav className="container mx-auto px-4 py-3 flex flex-col gap-1">
          {menuItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm${isActive ? ' bg-white/10' : ''}`
              }
              onClick={() => setMobileMenuOpen(false)}
            >
              {item.label}
            </NavLink>
          ))}
          <hr className="border-white/20 my-2" />

          {isAuthenticated ? (
            <>
              <div className="px-3 py-2 text-sm text-white/80">{displayName}</div>
              <Link
                to="/account"
                className="px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                onClick={() => setMobileMenuOpen(false)}
              >
                Mon Compte
              </Link>
              <button
                type="button"
                onClick={() => {
                  handleLogout();
                  setMobileMenuOpen(false);
                }}
                className="text-left px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
              >
                Se déconnecter
              </button>
            </>
          ) : (
            <>
              <Link
                to="/login"
                className="px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                onClick={() => setMobileMenuOpen(false)}
              >
                Se connecter
              </Link>
              <Link
                to="/register"
                className="px-3 py-2 rounded-md text-white hover:bg-white/10 transition-colors duration-200 text-sm"
                onClick={() => setMobileMenuOpen(false)}
              >
                S'inscrire
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}

