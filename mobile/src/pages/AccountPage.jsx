import React from 'react';
import { useAuth } from '../auth/AuthContext';
import { PageShell } from '../components/PageShell';

function getBackendBaseUrl() {
  return (import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_TARGET || '').replace(/\/$/, '');
}

function backendUrl(path) {
  const base = getBackendBaseUrl();
  return `${base}${path.startsWith('/') ? path : `/${path}`}`;
}

export function AccountPage() {
  const { user } = useAuth();
  const isAdmin = Array.isArray(user?.roles) ? user.roles.includes('ROLE_ADMIN') : false;

  return (
    <PageShell title="Mon Compte" subtitle="Gérez vos informations et votre sécurité">
      <div className="grid-2">
        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Informations du Compte</h2>
          <div className="stack">
            <div>
              <p className="text-xs text-gray-600 dark:text-gray-400 uppercase">Email</p>
              <p className="font-medium break-all">{user?.email || '—'}</p>
            </div>
            <div>
              <p className="text-xs text-gray-600 dark:text-gray-400 uppercase">Nom</p>
              <p className="font-medium">{user?.name || 'Non défini'}</p>
            </div>
            <div>
              <p className="text-xs text-gray-600 dark:text-gray-400 uppercase">Statut</p>
              <p className="font-medium">
                {isAdmin ? (
                  <span className="badge bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Administrateur</span>
                ) : (
                  <span className="badge bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Utilisateur</span>
                )}
              </p>
            </div>
          </div>
          <a
            href={backendUrl('/account/edit')}
            className="button button--primary block mt-4 text-center"
          >
            Modifier les Informations
          </a>
        </div>

        <div className="card">
          <h2 className="text-lg font-semibold mb-4">Sécurité</h2>
          <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Changez votre mot de passe pour sécuriser votre compte.
          </p>
          <a
            href={backendUrl('/account/password')}
            className="button button--primary block text-center"
          >
            Changer le Mot de Passe
          </a>
        </div>
      </div>

      <div className="card" style={{ borderColor: 'var(--color-alert)', background: 'rgba(211, 47, 47, 0.1)' }}>
        <h2 className="text-lg font-semibold mb-3 text-alert">Zone Dangereuse</h2>
        <p className="text-sm text-gray-700 dark:text-gray-300 mb-4">
          La suppression de compte reste disponible depuis la version web. Pour la version mobile, utilisez la page web de
          votre compte si vous souhaitez effectuer cette action irréversible.
        </p>
        <a
          href={backendUrl('/account')}
          className="inline-flex items-center gap-2 bg-alert text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity"
        >
          Ouvrir la page web du compte
        </a>
      </div>

      {isAdmin ? (
        <div className="card">
          <a
            href={backendUrl('/admin/users')}
            className="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-2a6 6 0 0112 0v2zm6-11a2 2 0 11-4 0 2 2 0 014 0zm-1 6h6v-2a4 4 0 00-4-4h-2v6z" />
            </svg>
            Gérer les Utilisateurs
          </a>
        </div>
      ) : null}
    </PageShell>
  );
}

